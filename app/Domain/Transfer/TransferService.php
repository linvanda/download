<?php

namespace App\Domain\Transfer;

use App\Domain\Task\Task;
use App\ErrCode;
use App\Foundation\File\LocalFile;
use EasySwoole\Utility\Random;
use WecarSwoole\Container;
use WecarSwoole\Exceptions\Exception;

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

        // 删除本地目标文件
        LocalFile::deleteDir($task->target()->getBaseDir());
    }

    /**
     * 从远程或本地存储下载目标文件
     * @return string 本地文件名称
     */
    public function fetchToLocal(Task $task): string
    {
        if (!$task->isSuccessed()) {
            throw new Exception("任务未处理成功：{$task->id()}", ErrCode::TASK_NOT_EXISTS);
        }

        return (new Download())->pull($task->id(), $task->target()->targetFileName());
    }

    /**
     * 生成临时下载 url
     */
    public function buildDownloadUrl(string $taskId, string $url): string
    {
        $ticket = new DownloadTicket(Random::character(64), $taskId);
        Container::get(ITransferRepository::class)->addDownloadTicket($ticket);

        return $url . (strpos($url, '?') === false ? '?' : '&') . "ticket={$ticket->ticket()}";
    }
}
