<?php

namespace App\Domain\Transfer;

use App\Domain\Task\Task;
use App\ErrCode;
use App\Foundation\File\LocalFile;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Utility\Random;
use WecarSwoole\Exceptions\Exception;

/**
 * 数据传输服务，用于处理目标文件的上传、下载
 */
class TransferService
{
    /**
     * @var ITransferRepository
     */
    private $transferRepository;

    public function __construct(ITransferRepository $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

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
        $this->transferRepository->saveDownloadTicket($ticket);

        return $url . (strpos($url, '?') === false ? '?' : '&') . "ticket={$ticket->ticket()}";
    }

    /**
     * 检查下载请求的合法性，防止恶意攻击
     */
    public function checkDownloadValidity(Task $task)
    {
        $taskId = $task->id();

        if (!$task->isSuccessed()) {
            throw new Exception("download fail:task is not done:$taskId", ErrCode::DOWNLOAD_FAILED);
        }

        $limitFor10min = Config::getInstance()->getConf('download_10m_limit');
        $downloadExpire = Config::getInstance()->getConf('download_expire');

        if ($task->finishedTime() + $downloadExpire < time()) {
            throw new Exception("download fail:task expired:$taskId", ErrCode::DOWNLOAD_FAILED);
        }

        $downloadTimer = $this->transferRepository->getDownloadTimer($taskId);

        if ($downloadTimer && $downloadTimer->times() >= $limitFor10min) {
            throw new Exception("download fail:operate too frequently,taskID:{$taskId}", ErrCode::DOWNLOAD_FAILED);
        }
    }

    /**
     * 记录任务下载次数
     */
    public function incrDownloadTimes(string $taskId)
    {
        if (!$downloadTimer = $this->transferRepository->getDownloadTimer($taskId)) {
            $downloadTimer = new DownloadTimer($taskId);
        }

        $downloadTimer->increase();

        $this->transferRepository->saveDownloadTimer($downloadTimer);
    }
}
