<?php

namespace App\Processor;

use App\Domain\Task\Task;
use App\Foundation\Queue\Queue;
use App\Processor\WorkFlow\WorkFlow;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Queue\Job;
use Psr\Log\LoggerInterface;
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

    private $workFlows = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    private function __construct()
    {
        $this->logger = Container::get(LoggerInterface::class);
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
        try {
            /**
             * 启动工作流后有可能一直阻塞在这里直到工作流执行结束（同步模式），也有可能工作流内部
             * 使用了异步处理，这里立马返回（但工作流仍然在执行）
             */
            $this->logger->info("开始处理任务：{$task->id()}");
            $this->getWorkFlow($task)->start();
        } catch (\Exception $e) {
            $this->logger->error("任务{$task->id()}处理异常：{$e->getMessage()}");
            $this->clear($task);
        }
    }

    /**
     * 任务处理结束
     */
    public function finish(Task $task)
    {
        $this->logger->info("任务处理结束：{$task->id()}，任务状态：{$task->status()}");
        $this->clear($task);
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
