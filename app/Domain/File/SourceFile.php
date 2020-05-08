<?php

namespace App\Domain\File;

use App\ErrCode;
use App\Exceptions\FileException;
use WecarSwoole\Util\File;

/**
 * 源文件
 */
class SourceFile extends LocalFile
{
    private $file;

    public function __construct(string $taskId)
    {
        parent::__construct($taskId);

        $this->file = $this->openFile('w');
    }

    /**
     * 存储文件
     * @param array $dataList 一维或二维数组
     */
    public function saveData(array $dataList)
    {
        $dataList = self::formatDataList($dataList);

        foreach ($dataList as $item) {
            if (fputcsv($this->file, $item) === false) {
                throw new FileException("写入源文件失败:{$this->saveToFile}", ErrCode::FILE_OP_FAILED);
            }
        }
    }

    protected function fullFileName(): string
    {
        return File::join($this->baseDir(), 'source.csv');
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
}
