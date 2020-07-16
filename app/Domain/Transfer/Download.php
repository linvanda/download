<?php

namespace App\Domain\Transfer;

use App\Domain\Target\Target;
use App\ErrCode;
use EasySwoole\EasySwoole\Config;
use OSS\OssClient;
use WecarSwoole\Exceptions\Exception;
use WecarSwoole\Util\File;

class Download
{
    /**
     * 下载文件
     * 如果本地存在，则什么都不做，没有的话再从阿里云 OSS 下载
     * @param string $taskId 任务编号
     * @param 本地原始文件名（未经过压缩的）
     * @return string 真实存在的本地文件名
     */
    public function download(string $taskId, string $targetFile): string
    {
        $realLocalFile = '';

        if (file_exists($targetFile)) {
            $realLocalFile = $targetFile;
        } elseif (file_exists($zipFile = $this->zipFile($targetFile))) {
            $realLocalFile = $zipFile;
        } else {
            // 从 OSS 拉数据到本地
            $realLocalFile = $this->fetchFileFromOSS($taskId, dirname($targetFile), explode('.', $targetFile)[1]);
        }

        return $realLocalFile;
    }

    private function zipFile(string $origFile): string
    {
        switch (Config::getInstance()->getConf('zip_type')) {
            case COMPRESS_TYPE_ZIP:
            default:
                $ext = 'zip';
                break;
        }

        return explode('.', $origFile)[0] . '.' . $ext;
    }

    /**
     * 从 OSS 下载文件到本地
     * @return string 下载到本地后在本地的绝对文件名
     */
    private function fetchFileFromOSS(string $taskId, string $localDir, string $ext): string
    {
        $config = Config::getInstance();

        $accessKey = $config->getConf('oss_access_key');
        $accessSecret = $config->getConf('oss_access_secret');
        $endpoint = $config->getConf('oss_endpoint');
        $bucket = $config->getConf('oss_bucket');
        $remoteName = $taskId . '.' . $ext;

        $client = new OssClient($accessKey, $accessSecret, $endpoint);

        if (!$client->doesObjectExist($bucket, $remoteName)) {
            $remoteName = $this->zipFile($taskId);
            if (!$client->doesObjectExist($bucket, $remoteName)) {
                throw new Exception("要下载的文件不存在,taskId:$taskId", ErrCode::DOWNLOAD_FAILED);
            }
        }

        // 下载到本地
        $localFile = File::join($localDir, 'object', explode('.', $remoteName)[1]);
        $client->getObject($bucket, $remoteName, [OssClient::OSS_FILE_DOWNLOAD => $localFile]);

        return $localFile;
    }
}
