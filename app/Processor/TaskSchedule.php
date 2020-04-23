<?php

namespace App\Processor;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Queue\Job;
use App\Domain\Task\Task;
use App\Domain\Task\TaskService;
use App\Foundation\Queue\Queue;
use EasySwoole\Component\Singleton;
use WecarSwoole\Container;

/**
 * 任务调度器
 */
class TaskSchedule
{
    use Singleton;

    private function __constructor()
    {
    }

    /**
     * 投递任务
     * 直接投递到消息队列中
     */
    public function deliver(Task $task)
    {
        $job = new Job();
        $job->setJobData(['task_id' => $task->id(), 'enqueue_time' => time()]);
        Queue::instance(Config::getInstance()->getConf('task_queue'))->producer()->push($job);
    }

    /**
     * 调度任务
     * 创建新协程处理任务
     * 通过 channel 控制最大协程数
     */
    public function schedule(string $taskId)
    {
        Ticket::get("task");

        // 执行任务之前将任务状态改成“处理中”
        Container::get(TaskService::class)->switchStatus($taskId, Task::STATUS_DOING);

        // 交给工作协程拉取数据源

    }
}
