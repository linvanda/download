<?php

namespace App\Domain\Object;

/**
 * 目标文件：CSV
 */
class CSV extends Object
{
    public function __construct(string $fileName = '', $tpl)
    {
        parent::__construct($fileName, self::TYPE_CSV, $tpl);
    }
}
