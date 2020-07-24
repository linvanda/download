<?php

namespace App\Foundation\DTO;

use WecarSwoole\DTO;

/**
 * 数据传输对象，给外部传参用
 */
class TaskDTO extends DTO
{
    public $id;
    public $name;
    public $sourceUrl;
    public $projectId;
    public $fileName;
    public $type;
    public $callback;
    public $step;
    public $operatorId;
    public $template;
    public $title;
    public $summary;
    public $header;
    public $footer;
    public $isSync;
    public $defaultWidth;// Excel 默认列宽度，单位 pt
    public $defaultHeight;// Excel 默认行高，单位 pt
    public $maxExecTime;// 任务处理时限（超过该时限还在“处理中”的任务将重新入列）

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        if (is_string($this->template)) {
            $this->template = $this->template ? json_decode($this->template, true) : [];
        }
    }
}
