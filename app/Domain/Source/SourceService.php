<?php

namespace App\Domain\Source;

use App\Foundation\File\LocalFile;
use App\Domain\Task\Task;
use App\Foundation\Client\API;

/**
 * 源数据服务
 */
class SourceService
{
    /**
     * 获取数据
     */
    public function fetch(Task $task)
    {
        $task->source()->fetch(new API(), new LocalFile($task->source()->fileName()));
    }
}
