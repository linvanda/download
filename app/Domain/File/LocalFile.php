<?php

namespace App\Domain\File;

use App\ErrCode;
use App\Exceptions\FileException;

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
            if (fputcsv($this->file, $item) === false) {
                throw new FileException("写入源文件失败:{$this->saveToFile}", ErrCode::FILE_OP_FAILED);
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

    private static function formatDataList(array $dataList): array
    {
        reset($dataList);

        if (!isset($dataList[0]) || !is_array($dataList[0])) {
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
            throw new FileException("打开文件失败:{$fileName}", ErrCode::FETCH_SOURCE_FAILED);
        }

        $this->file = $file;
    }
}
