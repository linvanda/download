<?php

namespace App\Domain\Object;

use App\Domain\Object\Template\Excel\TableTplFactory;
use App\Domain\Object\Template\Excel\TableTpl;

/**
 * 目标文件：Excel
 */
class Excel extends Object
{
    protected $title;
    protected $summary;
    // 表头
    protected $header = [];
    // 表尾
    protected $footer = [];
    /**
     * 表格模板
     * @var TableTpl
     */
    protected $tableTpl;

    public function __construct(string $fileName = '', $tableTpl = null, string $title = '', string $summary = '')
    {
        parent::__construct($fileName, self::TYPE_EXCEL);

        $this->title = $title;
        $this->summary = $summary;
        $this->setTableTpl($tableTpl);
    }

    /**
     * 表格模板
     */
    public function setTableTpl($tableTpl)
    {
        $this->tableTpl = $tableTpl === null || $tableTpl instanceof TableTpl ? $tableTpl : TableTplFactory::build($tableTpl);
    }

    public function tableTpl(): TableTpl
    {
        return $this->tableTpl;
    }

    /**
     * 表格标题
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * 表格摘要
     */
    public function summary(): string
    {
        return $this->summary;
    }

    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    public function header(): array
    {
        return $this->header;
    }

    public function setFooter(array $footer)
    {
        $this->footer = $footer;
    }

    public function footer(): array
    {
        return $this->footer;
    }
}
