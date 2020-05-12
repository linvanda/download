<?php

namespace App\Domain\Object;

/**
 * 目标文件：CSV
 */
class CSV extends Obj
{
    public function __construct(string $baseDir, string $downloadFileName = '')
    {
        parent::__construct($baseDir, $downloadFileName, self::TYPE_CSV);
    }
}
