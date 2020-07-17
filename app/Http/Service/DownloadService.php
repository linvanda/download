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
        $transferService = Container::get(TransferService::class);

        $transferService->checkDownloadValidity($taskId);

        if (!$task = Container::get(ITaskRepository::class)->getTaskById($taskId)) {
            throw new Exception("任务不存在：{$taskId}", ErrCode::DOWNLOAD_FAILED);
        }

        $localFile = $transferService->fetchToLocal($task);
        $downloadName = explode('.', $task->target()->downloadFileName())[0] . '.' . explode('.', $localFile)[1];

        set_time_limit(0);
        $response->withHeader("Content-Disposition", "attachment; filename=$downloadName");
        $response->sendFile($localFile);

        // 记录下载次数
        $transferService->incrDownloadTimes($taskId);
    }

    /**
     * 通过 ticket 下载资源
     */
    public function downloadWithTicket(string $ticketId, Response $response)
    {
        if (!$ticket = Container::get(ITransferRepository::class)->getDownloadTicket($ticketId)) {
            throw new Exception("download fail:ticket not exist or expired", ErrCode::DOWNLOAD_FAILED);
        }

        if (!$ticket->isValid()) {
            throw new Exception("download fail:ticket expired", ErrCode::DOWNLOAD_FAILED);
        }

        $this->download($ticket->taskId(), $response);
    }
}
