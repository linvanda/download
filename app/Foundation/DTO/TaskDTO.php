<?php

namespace App\Foundation\DTO;

use WecarSwoole\DTO;

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
    public $defaultWidth;// Excel 默认列宽度，单位 pt
    public $defaultHeight;// Excel 默认行高，单位 pt
    public $ctime;// 创建时间
    public $etime;// 最后执行时间
    public $ftime;// 执行完成时间
    public $stime;// 最后状态变更时间
    public $status;// 状态
    public $retryNum;// 重试次数

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        if (is_string($this->template)) {
            $this->template = $this->template ? json_decode($this->template, true) : [];
        }
    }
}
