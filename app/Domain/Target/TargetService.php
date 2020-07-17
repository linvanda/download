<?php

namespace App\Domain\Target;

use App\Foundation\File\Zip;
use App\Domain\Target\Generator\CSVGenerator;
use App\Domain\Target\Generator\ExcelGenerator;
use App\Domain\Task\Task;
use App\ErrCode;
use App\Exceptions\TargetException;
use App\Foundation\Client\API;
use EasySwoole\EasySwoole\Config;
use WecarSwoole\Util\File;

/**
 * 目标文件服务
 */
class TargetService
{
    /**
     * 生成目标文件
     */
    public function generate(Task $task)
    {
        switch ($task->target()->type()) {
            case Target::TYPE_CSV:
                $generator = new CSVGenerator();
                break;
            case Target::TYPE_EXCEL:
                $generator = new ExcelGenerator();
                break;
            default:
                throw new TargetException("不支持的目标文件类型：{$task->target()->type()}", ErrCode::FILE_TYPE_ERR);
        }

        switch (Config::getInstance()->getConf('zip_type')) {
            case COMPRESS_TYPE_ZIP:
            default:
                $compress = new Zip();
                break;
        }

        $generator->generate($task->source(), $task->target(), $compress);
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
        $task->target()->setMeta($metaData);
    }
}
