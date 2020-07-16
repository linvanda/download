<?php

namespace App\Domain\Transfer;

use App\Domain\Task\Task;

/**
 * 数据传输服务，用于处理目标文件的上传、下载
 */
class TransferService
{
    /**
     * 将本地目标文件上传到远程存储
     */
    public function upload(Task $task)
    {
        (new Upload())->upload($task->target()->targetFileName(), $task->id());
    }

    /**
     * 从远程或本地存储下载目标文件
     */
    public function download(Task $task)
    {

    }
}
