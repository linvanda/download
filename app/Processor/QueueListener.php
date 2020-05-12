<?php

namespace App\Processor;

use App\Domain\Task\ITaskRepository;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Queue\Job;
use App\Foundation\Queue\Queue;
use Psr\Log\LoggerInterface;
use WecarSwoole\Container;

/**
 * 队列监听
 */
class QueueListener
{
    public static function listen()
    {
        /**
         * task 队列监听
         */
        Queue::instance(Config::getInstance()->getConf('task_queue'))->consumer()->listen(function (Job $job) {
            // data 格式：['task_id' => '13112sdas', 'enqueue_time' => 23234223423]
            $data = $job->getJobData();
            if (!$data || !isset($data['task_id'])) {
                return;
            }

            if (!$task = Container::get(ITaskRepository::class)->getTaskById($data['task_id'])) {
                Container::get(LoggerInterface::class)->error("处理任务失败：任务不存在：{$data['task_id']}");
                return;
            }
            // 交给任务管理器处理
            TaskManager::getInstance()->process($task);
        }, 0.1);
    }
}
