<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 目标文件生成失败处理程序
 */
class ObjectFailedHandler extends WorkHandler
{
    protected function handleStatus(): int
    {
        return WorkFlow::WF_OBJECT_FAILED;
    }

    /**
     * 更改数据库状态为处理失败并标记处理次数
     */
    protected function exec()
    {
        
    }
}
