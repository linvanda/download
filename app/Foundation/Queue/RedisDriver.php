<?php

namespace App\Foundation\Queue;

use EasySwoole\Queue\Job;
use EasySwoole\Queue\QueueDriverInterface;
use WecarSwoole\RedisFactory;

/**
 * 消息队列：Redis 驱动
 */
class RedisDriver implements QueueDriverInterface
{
    protected $redis;
    protected $queueName;

    public function __construct(string $queueName, string $redisAlias = 'queue')
    {
        $this->redis = RedisFactory::build($redisAlias);
        $this->queueName = $queueName;
    }
    
    public function push(Job $job): bool
    {
        return $this->redis->lPush($this->redisKey(), json_encode($job->getJobData()));
    }

    public function pop(float $timeout = 3.0): ?Job
    {
        if ($data = json_decode($this->redis->rPop($this->redisKey()), true)) {
            $job = new Job();
            $job->setJobData($data);
            return $job;
        }

        return null;
    }

    public function size():?int
    {
        return $this->redis->lLen($this->redisKey());
    }

    protected function redisKey(): string
    {
        return "download-queue-{$this->queueName}";
    }
}
