<?php

namespace App\Domain\Target;

use App\Domain\Target\Template\Excel\Tpl;

/**
 * 目标文件：Excel
 */
class Excel extends Target
{
    // 表格标题
    protected $title;
    // 摘要
    protected $summary;
    // 表头
    protected $header;
    // 表尾
    protected $footer;
    // 默认列宽度
    protected $defaultWidth;
    // 默认行高度
    protected $defaultHeight;
    /**
     * 表格模板
     * @var Tpl
     */
    protected $template;

    public function __construct(string $baseDir, string $downloadFileName = '')
    {
        parent::__construct($baseDir, $downloadFileName, self::TYPE_EXCEL);
    }

    public function getTitle(): string
    {
        return $this->title ?: '';
    }

    public function getSummary(): string
    {
        return $this->summary ?: '';
    }

    public function getHeader(): array
    {
        return $this->header ?: [];
    }

    public function getFooter(): array
    {
        return $this->footer ?: [];
    }

    public function getDefaultWidth(): int
    {
        return $this->defaultWidth ?: 14;
    }

    public function getDefaultHeight(): int
    {
        return $this->defaultHeight ?: 14;
    }

    public function getTpl(): Tpl
    {
        return $this->template;
    }

    /**
     * 重写 setMeta，将数组中的各部分赋值给相应的属性，让其更具有语义
     * 注意，此处是增量覆盖
     */
    public function setMeta(array $metaData)
    {
        $this->title = $metaData['title'] ?? $this->title;
        $this->summary = $metaData['summary'] ?? $this->summary;
        $this->defaultWidth = $metaData['default_width'] ?? $this->defaultWidth;
        $this->defaultHeight = $metaData['default_height'] ?? $this->defaultHeight;

        if (isset($metaData['header']) && $metaData['header']) {
            $this->header = is_string($metaData['header']) ? json_decode($metaData['header'], true) : $metaData['header'];
        }
        
        if (isset($metaData['footer']) && $metaData['footer']) {
            $this->footer = is_string($metaData['footer']) ? json_decode($metaData['footer'], true) : $metaData['footer'];
        }

        if (isset($metaData['template'])) {
            $this->setTpl($metaData['template']);
        } elseif (isset($metaData['data']) && !$this->template) {
            // 如果没有静态 template，且有提供源数据，则试图从源数据解析出模板
            $this->setTpl(Tpl::getDefaultTplFromData($metaData['data']));
        }

        $this->metaData = $this->getMeta();
    }

    /**
     * 重写 getMeta
     */
    public function getMeta(string $key = '')
    {
        $data = [
            'title' => $this->title,
            'summary' => $this->summary,
            'header' => $this->header,
            'footer' => $this->footer,
            'template' => $this->template,
            'default_width' => $this->getDefaultWidth(),
            'default_height' => $this->getDefaultHeight(),
        ];

        return $key ? ($data[$key] ?? null) : $data;
    }

    /**
     * 表格模板
     */
    private function setTpl($template)
    {
        $this->template = $template === null || $template instanceof Tpl ? $template : Tpl::build($template);
    }
}
