<?php

namespace App\Foundation\File;

use App\ErrCode;
use App\Exceptions\FileException;
use WecarSwoole\Util\File;

/**
 * 本地文件
 */
class LocalFile
{
    private $file;
    private $fileName;

    public function __construct(string $fileName, string $mode = 'w+')
    {
        $this->openFile($fileName, $mode);
        $this->fileName = $fileName;
    }

    public function __destruct()
    {
        if ($this->file && is_resource($this->file)) {
            fclose($this->file);
        }
    }

    /**
     * 存储数据到 csv 文件
     * @param array $dataList 一维或二维数组
     */
    public function saveAsCsv(array $dataList)
    {
        $dataList = self::formatDataList($dataList);
        
        foreach ($dataList as $item) {
            if (@fputcsv($this->file, $item) === false) {
                throw new FileException("写入csv文件失败:{$this->fileName}", ErrCode::FILE_OP_FAILED);
            }
        }
    }

    /**
     * 文件大小
     */
    public function size(): int
    {
        return filesize($this->fileName);
    }

    public function close()
    {
        fclose($this->file);
    }

    /**
     * 删除目录（包括目录里面的文件）
     * @return bool 删除成功返回 true，失败 false
     */
    public static function deleteDir(string $dir): bool
    {
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }

        // 删除目录下所有文件（理论上只有一个）
        foreach (scandir($dir) as $fileOrDir) {
            if ($fileOrDir == '.' || $fileOrDir == '..') {
                continue;
            }

            $file = File::join($dir, $fileOrDir);
            if (is_file($file)) {
                unlink($file);
            } else {
                self::deleteDir($fileOrDir);
            }
        }

        // 删除空目录
        rmdir($dir);

        return true;
    }

    private static function formatDataList(array $dataList): array
    {
        if (!$dataList) {
            return [];
        }

        if (!is_array(reset($dataList))) {
            $dataList = [$dataList];
        }

        return array_map(function ($item) {
            return array_values($item);
        }, $dataList);
    }

    protected function openFile(string $fileName, string $mode)
    {
        $dir = dirname($fileName);
        if (!file_exists($dir)) {
            mkdir($dir, 0744, true);
        }

        $file = fopen($fileName, $mode);
        if ($file === false) {
            throw new FileException("打开文件失败:{$fileName}", ErrCode::FILE_OP_FAILED);
        }

        $this->file = $file;
    }
}
