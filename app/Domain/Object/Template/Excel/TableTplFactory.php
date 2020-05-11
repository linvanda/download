<?php

namespace App\Domain\Object\Template\Excel;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

/**
 * 表格模板工厂
 */
class TableTplFactory
{
    /**
     * @param array|string $tplCfg 模板配置
     */
    public static function build($tplCfg): ?TableTpl
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

        return new TableTpl(self::buildColHead($colCfg), self::buildRowHead($rowCfg));
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
