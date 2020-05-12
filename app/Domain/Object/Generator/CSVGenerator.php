<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Obj;
use App\Domain\Source\Source;
use App\Exceptions\FileException;

/**
 * CSV 文件生成器
 */
class CSVGenerator implements IGenerator
{
    /**
     * CSV 目标文件生成方式：直接将源文件重命名为目标文件
     */
    public function generate(Source $source, Obj $object)
    {
        $sourceFileName = $source->fileName();
        if (!$sourceFileName || !file_exists($sourceFileName)) {
            throw new FileException("CSV 目标文件生成失败：源文件不存在。source：{$sourceFileName}");
        }
        
        rename($sourceFileName, $object->objectFileName());
    }
}
