<?php

namespace App\Http\Service;

use App\Domain\Task\ITaskRepository;
use App\Domain\Transfer\ITransferRepository;
use App\Domain\Transfer\TransferService;
use App\ErrCode;
use EasySwoole\Http\Response;
use WecarSwoole\Container;
use WecarSwoole\Exceptions\Exception;

/**
 * 下载服务
 * 应用层服务
 */
class DownloadService
{
    /**
     * 下载文件
     */
    public function download(string $taskId, Response $response)
    {
        $this->checkValidity($taskId);

        if (!$task = Container::get(ITaskRepository::class)->getTaskById($taskId)) {
            throw new Exception("任务不存在：{$taskId}", ErrCode::DOWNLOAD_FAILED);
        }

        $localFile = Container::get(TransferService::class)->fetchToLocal($task);

        set_time_limit(0);
        $response->withHeader("Content-Disposition", "attachment; filename={$task->target()->downloadFileName()}");
        $response->sendFile($localFile);
    }

    /**
     * 通过 ticket 下载资源
     */
    public function downloadWithTicket(string $ticketId, Response $response)
    {
        if (!$ticket = Container::get(ITransferRepository::class)->getDownloadTicket($ticketId)) {
            throw new Exception("下载失败：ticket 不存在或者已过期", ErrCode::DOWNLOAD_FAILED);
        }

        if (!$ticket->isValid()) {
            throw new Exception("下载失败：ticket 不存在或者已过期", ErrCode::DOWNLOAD_FAILED);
        }

        $this->download($ticket->taskId(), $response);
    }

    /**
     * 校验下载合法性
     * 不合法则抛出异常
     */
    private function checkValidity(string $taskId)
    {
        // TODO
    }
}
