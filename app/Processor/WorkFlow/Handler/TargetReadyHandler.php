<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 目标文件就绪处理程序
 */
class TargetReadyHandler extends WorkHandler
{
    public function handleStatus(): int
    {
        return WorkFlow::WF_OBJECT_READY;
    }

    /**
     * 上传到 CDN
     */
    protected function exec()
    {
        $this->notify(WorkFlow::WF_UPLOAD_SUC);
    }
}
