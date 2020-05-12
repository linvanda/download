<?php

namespace App\Domain\Object;

use App\Domain\Task\Task;
use App\Foundation\Client\API;

/**
 * 目标文件服务
 */
class ObjService
{
    /**
     * 生成目标文件
     */
    public function generate(Task $task)
    {

    }

    /**
     * 获取动态元数据
     * 动态元数据是指在生成源数据时动态生成的元数据，这些元数据一般取决于数据本身，因而需要动态生成
     */
    public function fetchDynamicMeta(Task $task)
    {
        if (!$metaData = $task->source()->fetchMeta(new API())) {
            return;
        }
        
        $task->object()->setMeta($metaData);
    }
}
