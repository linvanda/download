<?php

namespace App\Domain\File;

use App\ErrCode;
use App\Exceptions\FileException;
use EasySwoole\EasySwoole\Config;
use WecarSwoole\Util\File;

/**
 * 本地文件（源文件、目标文件）的操作类
 */
class LocalFile
{
    protected $taskId;

    public function __construct(string $taskId)
    {
        $this->taskId = $taskId;    
    }

    protected function baseDir(): string
    {
        return File::join(Config::getInstance()->getConf('local_file_base_dir'), "{$this->taskId}");
    }

    protected function openFile(string $mode)
    {
        $fileName = $this->fullFileName();
        $dir = basename($fileName);
        if (!file_exists($dir)) {
            mkdir($dir, 0644, true);
        }

        $file = fopen($fileName, $mode);
        if ($file === false) {
            throw new FileException("打开文件失败:{$fileName}", ErrCode::FETCH_SOURCE_FAILED);
        }

        return $file;
    }

    /**
     * 文件路径
     */
    abstract protected function fullFileName(): string;
}
