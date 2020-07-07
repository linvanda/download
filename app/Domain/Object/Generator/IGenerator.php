<?php

namespace App\Domain\Object\Generator;

use App\Domain\Object\Obj;
use App\Domain\Source\Source;

/**
 * 目标文件生成器接口
 */
interface IGenerator
{
    /**
     * 根据源和目标信息生成目标文件
     * @param Source $source 源
     * @param Obj $object 目标对象
     */
    public function generate(Source $source, Obj $object);
}
