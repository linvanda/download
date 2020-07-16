<?php

namespace App\Domain\Target\Generator;

use App\Foundation\File\ICompress;
use App\Domain\Target\CSV;
use App\Domain\Target\Template\Excel\RowHead;
use App\Domain\Source\Source;
use App\ErrCode;
use App\Exceptions\FileException;
use EasySwoole\EasySwoole\Config;
use Exception;
use WecarSwoole\Util\File;

/**
 * CSV 文件生成器
 */
class CSVGenerator
{
    /**
     * CSV 目标文件生成方式
     */
    public function generate(Source $source, CSV $target, ICompress $compress)
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

        if (!$targetFile = @fopen($target->targetFileName(), 'w')) {
            throw new FileException("打开目标文件失败：{$target->targetFileName()}", ErrCode::FILE_OP_FAILED);
        }
        fputcsv($targetFile, $colNames);

        while (!feof($sourceFile)) {
            if (!$rowData = fgetcsv($sourceFile)) {
                continue;
            }
            
            if ($rowHeadIndex !== false && isset($rowData[$rowHeadIndex])) {
                unset($rowData[$rowHeadIndex]);
            }
            fputcsv($targetFile, $rowData);
        }

        fclose($sourceFile);
        fclose($targetFile);
        unlink($sourceFileName);

        // 压缩
        if ($source->size() > Config::getInstance()->getConf("zip_threshold")) {
            $newTargetFileName = $compress->compress(File::join($target->getBaseDir(), 'target'), [$target->targetFileName()]);
            // 重新设置目标文件名字
            $target->setTargetFileName($newTargetFileName);
        }
    }
}
