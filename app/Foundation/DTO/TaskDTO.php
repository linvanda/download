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
    public $ctime;// 创建时间
    public $etime;// 最后执行时间
    public $ftime;// 执行完成时间
    public $status;// 状态
    public $execNum;// 执行次数

    public function __construct(array $data = [])
    {
        parent::__construct($data);

        if (is_string($this->template)) {
            $this->template = $this->template ? json_decode($this->template, true) : [];
        }
    }
}
