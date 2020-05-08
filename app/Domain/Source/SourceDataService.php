<?php

namespace App\Domain\Source;

use App\Domain\File\SourceFile;
use App\Domain\Task\Task;
use App\Foundation\Client\API;

/**
 * 源数据服务
 */
class SourceDataService
{
    public function fetch(Task $task)
    {
        // 获取元数据
        (new MetaData($task, new API($task->source()->uri()->url())))->fetch();
        // 获取数据
        (new SourceData(
            new API($task->source()->uri()->url()),
            new SourceFile($task->id()),
            $task->source()->step()
        ))->fetch();
    }
}
