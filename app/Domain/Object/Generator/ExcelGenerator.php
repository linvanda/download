<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Obj;
use App\Domain\Source\Source;
use App\ErrCode;
use App\Exceptions\FileException;
use EasySwoole\EasySwoole\Config;

/**
 * Excel 文件生成器
 * 根据源文件的行数和大小决定生成几个目标文件
 * 如果生成多个目标文件（或者单个文件达到一定尺寸），则执行归档压缩
 */
class ExcelGenerator implements IGenerator
{
    /**
     * 根据 count 和 size 确定需要生成多少个目标文件，每个文件有多少行
     */
    public function generate(Source $source, Obj $object)
    {
        if (!file_exists($source->fileName())) {
            throw new FileException("源文件不存在：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        list($fileNum, $fileRow) = $this->calcFileCount($source);
        $fileNames = $this->calcFileNames($object->downloadFileName(), $fileNum);

        if (!$sourceFile = @fopen($source->fileName(), 'r')) {
            throw new FileException("打开源文件失败：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        // 先从第一行读取列标题
        $colTitles = fgetcsv($sourceFile);
        // 生成 sheet 模板
        $sheetTpl = $this->createSheetTpl($source);

        foreach ($fileNames as $index => $fileName) {
            // 生成 excel。最后一个文件的 row 不做限制
            $row = $index < count($fileNames) - 1 ? $fileRow : PHP_INT_MAX;
            $this->createExcel($sourceFile, $fileName, $fileNum, $index, $row, $colTitles);
        }
    }

    private function createSheetTpl(Source $source)
    {
        
    }

    private function createExcel($sourceFile, string $objFileName, int $totalFileNum, int $index, int $row, array $colTitles)
    {
        $i = 0;
        while ($i++ < $row && !feof($sourceFile)) {
            if (!$colVal = fgetcsv($sourceFile)) {
                continue;
            }
        }
    }

    private function calcFileNames(string $origFileName, int $fileNum): array
    {
        if ($fileNum === 1) {
            return [$origFileName];
        }

        $fileNames = [];
        $fnameArr = explode('.', $origFileName);
        $ext = array_pop($fnameArr);
        $base = implode('.', $fnameArr);

        for ($i = 0; $i < $fileNum; $i++) {
            $fileNames[] = implode('', [$base, "_{$i}", ".{$ext}"]);
        }

        return $fileNames;
    }

    /**
     * 计算目标文件数目以及每个文件最大行数
     * @return array [文件数目, 最大行数]
     */
    private function calcFileCount(Source $source): array
    {
        $maxSize = Config::getInstance()->getConf("excel_max_size");
        $maxCount = Config::getInstance()->getConf("excel_max_count");
        $sourceSize = $source->size();
        $sourceCount = $source->count();

        if ($sourceSize <= $maxSize * 1.5 && $sourceCount <= $maxCount * 1.5) {
            return [1, PHP_INT_MAX];
        }

        $count = max(ceil($sourceSize / $maxSize), ceil($sourceCount / $maxCount));

        return [$count, ceil($sourceCount / $count)];
    }
}
