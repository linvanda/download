<?php

namespace App\Domain\File;

/**
 * 目标文件：CSV
 */
class CSV extends ObjectFile
{
    public function __construct(string $fileName = '', array $tplCfg = [])
    {
        parent::__construct($fileName, self::TYPE_CSV, $tplCfg);
    }
}