<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 目标文件就绪处理程序
 */
class ObjectReadyHandler extends WorkHandler
{
    protected function handleStatus(): int
    {
        return WorkFlow::WF_OBJECT_READY;
    }

    /**
     * 上传到 CDN
     */
    protected function exec()
    {
        
    }
}
