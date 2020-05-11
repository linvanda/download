<?php

namespace App\Domain\Object\Template\Excel;

/**
 * Excel 表格模板
 */
class TableTpl
{
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
     * @param array $data 一维数组，如 ["name" => "张三", "age" => 89]
     */
    public static function getDefaultTplFromData(array $data): array
    {
        return array_map(function ($title) {
            return ['name' => $title, 'title' => $title];
        }, array_keys($data));
    }
}
