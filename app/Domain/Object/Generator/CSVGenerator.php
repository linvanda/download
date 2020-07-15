<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\CSV;
use App\Domain\Object\Template\Excel\RowHead;
use App\Domain\Source\Source;
use App\ErrCode;
use App\Exceptions\FileException;
use Exception;

/**
 * CSV 文件生成器
 */
class CSVGenerator
{
    /**
     * CSV 目标文件生成方式
     */
    public function generate(Source $source, CSV $target)
    {
        $sourceFileName = $source->fileName();
        if (!$sourceFileName || !file_exists($sourceFileName)) {
            throw new FileException("CSV 目标文件生成失败：源文件不存在。source：{$sourceFileName}");
        }
        
        if (!$sourceFile = @fopen($sourceFileName, 'r')) {
            throw new FileException("打开源文件失败：{$source->fileName()}", ErrCode::FILE_OP_FAILED);
        }

        // 读取第一行作为列名
        if (!$colNames = fgetcsv($sourceFile)) {
            throw new Exception("源文件为空", ErrCode::SOURCE_DATA_EMPTY);
        }

        // 删除掉行表头列
        $rowHeadIndex = array_search(RowHead::NODE_ROW_HEADER_COL, $colNames);
        if ($rowHeadIndex !== false) {
            unset($colNames[$rowHeadIndex]);
        }

        if (!$objectFile = @fopen($target->objectFileName(), 'w')) {
            throw new FileException("打开目标文件失败：{$target->objectFileName()}", ErrCode::FILE_OP_FAILED);
        }
        fputcsv($objectFile, $colNames);

        while (!feof($sourceFile)) {
            if (!$rowData = fgetcsv($sourceFile)) {
                continue;
            }
            
            if ($rowHeadIndex !== false && isset($rowData[$rowHeadIndex])) {
                unset($rowData[$rowHeadIndex]);
            }
            fputcsv($objectFile, $rowData);
        }

        fclose($sourceFile);
        fclose($objectFile);
        unlink($sourceFileName);
    }
}
