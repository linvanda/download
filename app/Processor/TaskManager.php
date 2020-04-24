<?php

namespace App\Processor;

use App\Domain\Task\ITaskRepository;
use App\Domain\Task\Task;
use App\Domain\Task\TaskService;
use App\Exceptions\TaskNotFoundException;
use App\Foundation\DTO\TaskDTO;
use App\Foundation\Queue\Queue;
use App\Processor\WorkFlow\WorkFlow;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Queue\Job;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use WecarSwoole\Container;
use WecarSwoole\Exceptions\CriticalErrorException;
use WecarSwoole\Exceptions\Exception;

/**
 * 任务管理器
 * 任务处理工作流入口
 */
class TaskManager
{
    use Singleton;

    private $workFlows = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    private function __constructor(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 新任务
     */
    public function newTask(TaskDTO $taskDTO): Task
    {
        // 创建新任务
        $task = Container::get(TaskService::class)->create($taskDTO);

        // 入列
        $job = new Job();
        $job->setJobData(['task_id' => $this->workFlow->taskId(), 'enqueue_time' => time()]);
        Queue::instance(Config::getInstance()->getConf('task_queue'))->producer()->push($job);

        return $task;
    }

    /**
     * 启动工作流
     * @param string $taskId 任务 id
     */
    public function startWorkFlow(string $taskId)
    {
        try {
            $workFlow = $this->getWorkFlow($taskId);
            /**
             * 启动工作流后有可能一直阻塞在这里直到工作流执行结束（同步模式），也有可能工作流内部
             * 使用了异步处理，这里立马返回（但工作流仍然在执行）
             */
            $workFlow->start();
        } catch (CriticalErrorException $e) {
            // 发生致命异常，说明无法通过重试让任务成功
            Container::get(TaskService::class)->switchStatus($workFlow->task(), Task::STATUS_ERR);
            $this->clearWorkFlow($taskId, $e->getMessage(), Logger::CRITICAL);
        } catch (TaskNotFoundException $e) {
            // 任务不存在，则无需更改任务状态
            $this->clearWorkFlow($taskId, $e->getMessage());
        } catch (Exception $e) {
            // 其它异常，将任务标记为可重试型失败
            Container::get(TaskService::class)->switchStatus($workFlow->task(), Task::STATUS_FAILED);
            $this->clearWorkFlow($taskId, $e->getMessage(), Logger::ERROR);
        }
    }

    /**
     * 初始化工作流
     */
    private function initWorkFlow(string $taskId)
    {
        if (isset($this->workFlows[$taskId])) {
            return;
        }

        if (!$task = Container::get(ITaskRepository::class)->getTaskById($taskId)) {
            throw new TaskNotFoundException("任务不存在");
        }

        $this->workFlows[$taskId] = WorkFlow::newWorkFlow($task);
    }

    /**
     * 获取任务对应的工作流
     */
    private function getWorkFlow(string $taskId): WorkFlow
    {
        if (!isset($this->workFlows[$taskId])) {
            $this->initWorkFlow($taskId);
        }

        return $this->workFlows[$taskId];
    }

    /**
     * 清理工作流
     */
    private function clearWorkFlow(string $taskId, string $logMsg = '', int $logLevel = Logger::INFO)
    {
        // 清理工作流
        if (isset($this->workFlows[$taskId])) {
            unset($this->workFlows[$taskId]);
        }

        if (!$logMsg) {
            return;
        }

        // 记录日志
        if ($logLevel >= Logger::CRITICAL) {
            $this->logger->critical("task:{$taskId}执行结果：{$logMsg}");
        } else {
            $this->logger->log($logLevel, $logMsg);
        }
    }
}
