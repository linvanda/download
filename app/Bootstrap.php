<?php

namespace App;

use WecarSwoole\Bootstrap as BaseBootstrap;

/**
 * bootstrap 启动脚本会在 work/task 进程启动时执行
 * Class Bootstrap
 * @package App
 */
class Bootstrap extends BaseBootstrap
{
    /**
     * @throws \Throwable
     */
    public static function boot()
    {
        parent::boot();
    }
}
