<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 源数据就绪处理程序
 */
class SourceReadyHandler extends WorkHandler
{
    protected function handleStatus(): int
    {
        return WorkFlow::WF_SOURCE_READY;
    }

    /**
     * 投递给 task 进程生成目标数据
     */
    protected function exec()
    {
        
    }
}
