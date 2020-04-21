<?php

namespace App\Foundation\DTO;

use WecarSwoole\DTO;

class TaskDTO extends DTO
{
    public $sourceUrl;
    public $name;
    public $projectId;
    public $fileName;
    public $type;
    public $callback;
    public $step;
    public $operatorId;
    public $template;
    public $title;
    public $summary;
}
