<?php

namespace App\Domain\Object;

/**
 * 目标文件：CSV
 */
class CSV extends Obj
{
    public function __construct(string $fileName = '')
    {
        parent::__construct($fileName, self::TYPE_CSV);
    }
}
