<?php

namespace App\Processor;

use EasySwoole\EasySwoole\Config;
use Swoole\Coroutine\Channel;

/**
 * 限流票据
 */
final class Ticket
{
    private static $channels = [];

    /**
     * 获取票据
     */
    public static function get(string $group)
    {
        if (!isset(self::$channels[$group])) {
            self::$channels[$group] = new Channel(Config::getInstance()->getConf("task_concurrent_limit") ?: 10);
        }

        self::$channels[$group]->push(1);
    }

    /**
     * 归还票据
     */
    public static function done(string $group)
    {
        if (!isset(self::$channels[$group])) {
            return;
        }

        self::$channels[$group]->pop(0);
    }
}
