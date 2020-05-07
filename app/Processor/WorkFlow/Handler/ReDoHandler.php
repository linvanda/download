<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 失败重试处理程序
 */
class ReDoHandler extends WorkHandler
{
    public function handleStatus(): int
    {
        return WorkFlow::WF_REDO;
    }

    /**
     * 入列
     */
    protected function exec()
    {
        
    }
}
