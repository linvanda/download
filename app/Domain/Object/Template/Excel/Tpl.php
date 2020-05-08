<?php

namespace App\Domain\Object\Template\Excel;

/**
 * Excel 模板
 */
class Tpl
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
}
