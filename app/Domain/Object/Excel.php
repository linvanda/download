<?php

namespace App\Domain\Object;

use App\Domain\Object\Template\Excel\TplFactory;
use App\Domain\Object\Template\Excel\Tpl;

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
     * @var Tpl
     */
    protected $template;

    public function __construct(string $fileName = '', $template = null, string $title = '', string $summary = '')
    {
        parent::__construct($fileName, self::TYPE_EXCEL);

        $this->title = $title;
        $this->summary = $summary;
        $this->setTpl($template);
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
        if (isset($metaData['template'])) {
            $this->template = $this->setTpl($metaData['template']);
        } elseif ($metaData['data'] && !$this->template) {
            // 如果没有静态 template，且有提供源数据，则试图从源数据解析出模板
            $this->template = $this->setTpl(Tpl::getDefaultTplFromData($metaData['data']));
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
            'template' => $this->template,
        ];

        return $key ? ($data[$key] ?? null) : $data;
    }

    /**
     * 表格模板
     */
    private function setTpl($template)
    {
        $this->template = $template === null || $template instanceof Tpl ? $template : TplFactory::build($template);
    }
}
