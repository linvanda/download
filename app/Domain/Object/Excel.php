<?php

namespace App\Domain\Object;

/**
 * 目标文件：Excel
 */
class Excel extends ObjectFile
{
    protected $title;
    protected $summary;
    // 表头
    protected $header = [];
    // 表尾
    protected $footer = [];

    public function __construct(string $fileName = '', $tplCfg = null, string $title = '', string $summary = '')
    {
        parent::__construct($fileName, self::TYPE_EXCEL, $tplCfg);

        $this->title = $title;
        $this->summary = $summary;
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
