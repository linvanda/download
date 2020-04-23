<?php

namespace App\Processor;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Queue\Job;
use App\Foundation\Queue\Queue;

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

            // 交给任务处理器
            TaskManager::getInstance()->schedule($data['task_id']);
        }, 0.1);
    }
}
