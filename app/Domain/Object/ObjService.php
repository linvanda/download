<?php

namespace App\Domain\Object;

use App\Domain\Object\Generator\CSVGenerator;
use App\Domain\Object\Generator\ExcelGenerator;
use App\Domain\Task\Task;
use App\ErrCode;
use App\Exceptions\ObjectException;
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
        switch ($task->object()->type()) {
            case Obj::TYPE_CSV:
                $generator = new CSVGenerator();
                break;
            case Obj::TYPE_EXCEL:
                $generator = new ExcelGenerator();
                break;
            default:
                throw new ObjectException("不支持的目标文件类型：{$task->object()->type()}", ErrCode::FILE_TYPE_ERR);
        }

        $generator->generate($task->source(), $task->object());
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
        // 动态元数据不保存到数据库中
        $task->object()->setMeta($metaData);
    }
}
