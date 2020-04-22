<?php

namespace App\Domain\File\Template\Excel;

/**
 * Excel 模板
 */
class Tpl
{
    /**
     * @var Column 列标题
     */
    private $colHeader;
    /**
     * @var Row 行标题
     */
    private $rowHeader;

    public function __construct(Column $col, Row $row = null)
    {
        $this->colHeader = $col;
        $this->rowHeader = $row;
    }
}
