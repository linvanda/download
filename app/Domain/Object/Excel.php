<?php

namespace App\Domain\Object;

use App\Domain\Object\Template\Excel\TableTplFactory;
use App\Domain\Object\Template\Excel\TableTpl;

/**
 * 目标文件：Excel
 */
class Excel extends Obj
{
    // 表格标题
    protected $title;
    // 摘要
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
     * 重写 setMeta，将数组中的各部分赋值给相应的属性，让其更具有语义
     * 注意，此处是增量覆盖
     */
    public function setMeta(array $metaData)
    {
        if (isset($metaData['title'])) {
            $this->title = $metaData['title'];
        }
        if (isset($metaData['summary'])) {
            $this->summary = $metaData['summary'];
        }
        if (isset($metaData['header'])) {
            $this->header = $metaData['header'];
        }
        if (isset($metaData['footer'])) {
            $this->footer = $metaData['footer'];
        }
        if (isset($metaData['table_tpl'])) {
            $this->tableTpl = $this->setTableTpl($metaData['table_tpl']);
        }

        $this->metaData = $this->getMeta();
    }

    /**
     * 重写 getMeta
     * 
     */
    public function getMeta(string $key = '')
    {
        $data = [
            'title' => $this->title,
            'summary' => $this->summary,
            'header' => $this->header,
            'footer' => $this->footer,
            'table_tpl' => $this->tableTpl,
        ];

        return $key ? ($data[$key] ?? null) : $data;
    }

    /**
     * 表格模板
     */
    private function setTableTpl($tableTpl)
    {
        $this->tableTpl = $tableTpl === null || $tableTpl instanceof TableTpl ? $tableTpl : TableTplFactory::build($tableTpl);
    }
}
