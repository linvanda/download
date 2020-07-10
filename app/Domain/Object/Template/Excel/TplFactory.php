<?php

namespace App\Domain\Object\Template\Excel;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

/**
 * 表格模板工厂
 */
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

        $colHead = self::buildColHead($colCfg);
        $rowHead = self::buildRowHead($rowCfg);

        // 如果有 rowHead 且 colHead 中没有对应的列占位，则补齐
        if ($rowHead && !$colHead->search(Node::NODE_ROW_HEADER_COL)) {
            $rowHeadCol = new ColHead(Node::NODE_ROW_HEADER_COL, '', null, ColHead::DT_STR);
            $colHead->appendChild($rowHeadCol);
        }

        return new Tpl($colHead, $rowHead);
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
