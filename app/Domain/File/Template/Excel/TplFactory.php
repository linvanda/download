<?php

namespace App\Domain\File\Template\Excel;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

class TplFactory
{
    public static function build(array $tplCfg): Tpl
    {
        if (!isset($tplCfg['col']) && $tplCfg['row']) {
            throw new Exception("模板格式不合法", ErrCode::TPL_FMT_ERR);
        }

        $rowCfg = $tplCfg['row'] ?? [];
        $colCfg = $tplCfg['col'] ?? $tplCfg;

        return new Tpl(self::buildCol($colCfg), self::buildRow($rowCfg));
    }

    private static function buildRow(array $rowCfg): ?Row
    {
        if (!$rowCfg) {
            return null;
        }


    }

    private static function buildCol(array $colCfg): Column
    {
        if (!$colCfg) {
            throw new Exception("模板格式错误：缺少列标题配置", ErrCode::TPL_FMT_ERR);
        }

        
    }
}
