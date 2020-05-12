<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Obj;
use App\Domain\Source\Source;
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
        list($fileNum, $fileRow) = $this->calcFileCount($source);

        
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
