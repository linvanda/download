<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\WorkFlow\WorkFlow;

/**
 * 目标文件上传失败处理程序
 */
class UploadFailedHandler extends WorkHandler
{
    protected function handleStatus(): int
    {
        return WorkFlow::WF_UPLOAD_FAILED;
    }

    /**
     * 更改数据库状态为处理失败并标记处理次数
     */
    protected function exec()
    {
        
    }
}
