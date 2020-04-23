<?php

namespace App\Domain\File\Template\Excel;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

class TplFactory
{
    /**
     * @param array|string $tplCfg 模板配置
     */
    public static function build($tplCfg): ?Tpl
    {
        if ($tplCfg && is_string($tplCfg)) {
            $tplCfg = json_decode($tplCfg, true);
        }

        if (!$tplCfg) {
            return null;
        }

        if (!isset($tplCfg['col']) && isset($tplCfg['row'])) {
            throw new Exception("模板格式不合法", ErrCode::TPL_FMT_ERR);
        }

        $rowCfg = $tplCfg['row'] ?? [];
        $colCfg = $tplCfg['col'] ?? $tplCfg;

        return new Tpl(self::buildColHead($colCfg), self::buildRowHead($rowCfg));
    }

    private static function buildRowHead(array $rowCfg): ?RowHead
    {
        if (!$rowCfg) {
            return null;
        }

        return RowHeadParser::getInstance()->parse($rowCfg);
    }

    private static function buildColHead(array $colCfg): ColHead
    {
        if (!$colCfg) {
            throw new Exception("模板格式错误：缺少列标题配置", ErrCode::TPL_FMT_ERR);
        }

        return ColHeadParser::getInstance()->parse($colCfg);
    }
}
