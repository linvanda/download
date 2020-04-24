<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 源数据获取失败处理程序
 */
class SourceFailedHandler extends WorkHandler
{
    protected function handleStatus(): int
    {
        return WorkFlow::WF_SOURCE_FAILED;
    }

    /**
     * 启动新协程拉取源数据
     */
    protected function exec()
    {
        
    }
}
