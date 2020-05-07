<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 目标文件上传成功处理程序
 */
class UploadSuccessHandler extends WorkHandler
{
    public function handleStatus(): int
    {
        return WorkFlow::WF_UPLOAD_SUC;
    }

    /**
     * 通知客户端
     */
    protected function exec()
    {
        $this->notify(WorkFlow::WF_NOTIFY_DONE);
    }
}
