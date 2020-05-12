<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Obj;
use App\Domain\Source\Source;

/**
 * Excel 文件生成器
 */
class ExcelGenerator implements IGenerator
{
    public function generate(Source $source, Obj $object)
    {
        echo "generate excel\n";
    }
}
