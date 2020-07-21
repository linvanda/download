<?php

namespace App\Processor;

use App\Domain\Task\Task;
use App\Domain\Task\TaskService;
use App\Foundation\Queue\Queue;
use App\Processor\WorkFlow\WorkFlow;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Queue\Job;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine;
use WecarSwoole\Container;

/**
 * 任务管理器
 * 任务管理器是单例，用来维护本进程中多个任务的生命周期
 * 一个任务对应一个工作流，因而任务管理器也维护工作流的生命周期
 * 注意：工作流的概念属于内部细节，任务管理器不能将该内部概念暴露给外部
 */
class TaskManager
{
    use Singleton;

    // 工作中
    private const STATUS_WORKING = 1;
    // 等待重启中（等待其它任务完成，此时不再接收新任务）
    private const STATUS_STOP_WAITING = 2;
    // 重启中
    private const STATUS_STOPPING = 3;

    // 正在处理的工作流列表
    private $workFlows = [];
    // 该任务管理器共处理了多少任务
    private $procCount;
    /**
     * @var LoggerInterface
     */
    private $logger;
    private $status;

    private function __construct()
    {
        $this->procCount = 0;
        $this->logger = Container::get(LoggerInterface::class);
        $this->status = self::STATUS_WORKING;
    }

    /**
     * 投递任务
     */
    public function deliver(Task $task)
    {
        $job = new Job();
        $job->setJobData(['task_id' => $task->id(), 'enqueue_time' => time()]);
        Queue::instance(Config::getInstance()->getConf('task_queue'))->producer()->push($job);
        $this->logger->info("投递任务到消息队列：{$task->id()}");
    }

    /**
     * 处理任务
     */
    public function process(Task $task)
    {
        Ticket::get("task_source");

        // 在新的协程中执行
        go(function () use ($task) {
            // 将任务状态改成正在执行中
            Container::get(TaskService::class)->switchStatus($task, Task::STATUS_DOING);
            try {
                $this->logger->info("开始处理任务：{$task->id()}");
                $this->getWorkFlow($task)->start();
            } catch (\Exception $e) {
                $this->logger->error("任务{$task->id()}处理异常：{$e->getMessage()}");
                $this->clear($task);
            }
        });
    }

    /**
     * 结束任务
     * @param Task $task
     * @param int $result 处理结果：1 成功，2 失败
     * @param string $msg 失败的情况下，失败原因
     */
    public function finish(Task $task, int $result = 1, string $msg = '')
    {
        // 修改任务状态
        Container::get(TaskService::class)->switchStatus($task, $result == 1 ? Task::STATUS_SUC : Task::STATUS_FAILED, $msg);
        // 清理
        $this->clear($task);
        // 归还 ticket
        Ticket::done("task_source");
        $this->procCount++;

        $this->logger->info("任务处理结束：{$task->id()}，任务状态：{$task->status()}，msg：{$msg}");

        // 看是否需要重启任务管理器
        $this->tryToReboot();
    }

    private function tryToReboot()
    {
        if ($this->status != self::STATUS_WORKING || $this->procCount < Config::getInstance()->getConf('task_max_process')) {
            return;
        }

        $this->status = self::STATUS_STOP_WAITING;
        // 停止队列监听
        QueueListener::stop();

        // 循环检查工作流列表，当工作流列表为空，或者超过等待时间(15分钟)后重启当前进程
        $this->logger->info("进程服役期满，将进入重启.pid:" . getmypid());
        $cnt = 0;
        while (!empty($this->workFlows) && $cnt++ < 900) {
            Coroutine::sleep(1);
        }

        $this->stop();
    }

    private function stop()
    {
        $this->status = self::STATUS_STOPPING;
        $this->logger->info("进程重启.pid:" . getmypid());
        $server = ServerManager::getInstance()->getSwooleServer();
        $server->stop($server->worker_id, true);
    }

    /**
     * 获取任务对应的工作流
     */
    private function getWorkFlow(Task $task): WorkFlow
    {
        if (!isset($this->workFlows[$task->id()])) {
            $this->initWorkFlow($task);
        }

        return $this->workFlows[$task->id()];
    }
    
    /**
     * 初始化工作流
     */
    private function initWorkFlow(Task $task)
    {
        if (isset($this->workFlows[$task->id()])) {
            return;
        }

        $this->workFlows[$task->id()] = WorkFlow::newWorkFlow($task);
    }

    /**
     * 任务处理结束后的清理工作
     */
    private function clear(Task $task)
    {
        // 清理工作流
        if (isset($this->workFlows[$task->id()])) {
            $this->logger->info("清理工作流，任务{$task->id()}，工作流状态：" . $this->workFlows[$task->id()]->status());
            unset($this->workFlows[$task->id()]);
        }
    }
}
