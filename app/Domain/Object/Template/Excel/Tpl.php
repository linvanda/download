<?php

namespace App\Domain\Object\Template\Excel;

/**
 * Excel 表格模板
 */
class Tpl
{
    use TplBuilder;
    
    /**
     * @var ColHead 列标头
     */
    private $col;
    /**
     * @var RowHead 行标头
     */
    private $row;

    public function __construct(ColHead $colHead, RowHead $rowHead = null)
    {
        $this->col = $colHead;
        $this->row = $rowHead;
    }

    public function colHead(): ColHead
    {
        return $this->col;
    }

    public function rowHead(): ?RowHead
    {
        return $this->row;
    }

    /**
     * 从数据中解析默认模板
     * @param array $data 一维或二维数组，如 ["name" => "张三", "age" => 89]，[["name" => "张三", "age" => 89]]
     */
    public static function getDefaultTplFromData(array $data): array
    {
        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $cfg = [];

        foreach ($data as $key => $val) {
            if ($key == RowHead::NODE_ROW_HEADER_COL) {
                continue;
            }
            
            $cfg[] = [
                'name' => $key,
                'title' => $key,
                'type' => is_int($val) || is_float($val) ? ColHead::DT_NUM : ColHead::DT_STR,
            ];
        }

        return $cfg;
    }
}
