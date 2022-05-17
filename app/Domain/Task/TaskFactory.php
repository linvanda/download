<?php

namespace App\Domain\Task;

use App\Domain\Target\CSVTarget;
use App\Domain\Target\ExcelTarget;
use App\Domain\Target\Target;
use App\Domain\Project\IProjectRepository;
use App\Domain\Source\CSVSource;
use App\Domain\Source\ISource;
use App\Foundation\DTO\TaskDTO;
use EasySwoole\EasySwoole\Config;
use WecarSwoole\Container;
use WecarSwoole\Exceptions\Exception;
use WecarSwoole\ID\IIDGenerator;
use WecarSwoole\Util\File;
use App\Domain\URI;
use App\ErrCode;
use App\Foundation\DTO\DBTaskDTO;

/**
 * 工厂：创建 task 对象
 */
class TaskFactory
{
    public static function create(TaskDTO $taskDTO): Task
    {
        if (!$taskDTO->sourceUrl && !$taskDTO->sourceData) {
            throw new Exception("创建任务失败：source_url 和 source_data 需要提供一个", ErrCode::PARAM_VALIDATE_FAIL);
        }

        if (!$project = Container::get(IProjectRepository::class)->getProjectById($taskDTO->projectId)) {
            throw new Exception("创建任务失败：项目不存在", ErrCode::PROJ_NOT_EXISTS);
        }

        // 此处并没有对外界传入的 id 做唯一性校验，有调用方保证
        $id = $taskDTO->id ?? Container::get(IIDGenerator::class)->id();
        $taskDTO->id = $id;

        // 处理 source_data
        if ($taskDTO->sourceData) {
            $taskDTO->sourceData = is_string($taskDTO->sourceData) ? json_decode($taskDTO->sourceData, true) : $taskDTO->sourceData;
        }

        // multiType
        $taskDTO->multiType = $taskDTO->multiType ?? 'single';
        
        // 源
        $source = self::buildSource($taskDTO, $taskDTO->type ?? 'csv');
        // 目标
        $target = self::buildTarget($taskDTO);
        // 回调
        $callback = new URI($taskDTO->callback ?: '');

        // 基于 DTO 创建 Task 对象
        $task = new Task(
            $id,
            $taskDTO->name,
            $project,
            $source,
            $target,
            $callback,
            $taskDTO->operatorId ?: '',
            $taskDTO->maxExecTime ?: 0,
            $taskDTO->isSync ?: 0,
            new Merchant($taskDTO->merchantId ? intval($taskDTO->merchantId) : 0, $taskDTO->merchantType ? intval($taskDTO->merchantType) : 0)
        );

        if ($taskDTO instanceof DBTaskDTO) {
            // 来自存储层的数据，需要设置其他属性
            $task->createTime = $taskDTO->ctime;
            $task->lastExecTime = $taskDTO->etime;
            $task->finishedTime = $taskDTO->ftime;
            $task->lastEnqueueTime = $taskDTO->qtime;
            $task->lastChangeStatusTime = $taskDTO->stime;
            $task->status = $taskDTO->status;
            $task->retryNum = $taskDTO->retryNum;
        }

        return $task;
    }

    private static function buildSource(TaskDTO $taskDTO, string $targetType): ISource
    {
        $source = null;
        switch (strtolower($targetType)) {
            case 'csv':
            case 'excel': 
            default:
               $source = new CSVSource(
                    new URI($taskDTO->sourceUrl ?: ''),
                    $taskDTO->sourceData ?: [],
                    File::join(Config::getInstance()->getConf('local_file_base_dir'), $taskDTO->id),
                    $taskDTO->id,
                    intval($taskDTO->step) ?: CSVSource::STEP_DEFAULT,
                   $taskDTO->interval ?: CSVSource::DEFAULT_INTERVAL
                );
        }
        
        return $source;
    }

    private static function buildTarget(TaskDTO $taskDTO): Target
    {
        $baseDir = File::join(Config::getInstance()->getConf('local_file_base_dir'), $taskDTO->id);
        switch ($taskDTO->type ?: Target::TYPE_CSV) {
            case Target::TYPE_CSV:
                return new CSVTarget($baseDir, $taskDTO->fileName ?: '');
            case Target::TYPE_EXCEL:
                $excel = new ExcelTarget($baseDir, $taskDTO->fileName ?: '', $taskDTO->multiType);

                // multitype 多表模式需要对传入的 title、summary 做特殊处理
                if ($taskDTO->multiType !== ExcelTarget::MT_SINGLE) {
                    $taskDTO->title = is_string($taskDTO->title) ? json_decode($taskDTO->title, true) : $taskDTO->title;
                    $taskDTO->summary = is_string($taskDTO->summary) ? json_decode($taskDTO->summary, true) : $taskDTO->summary;
                }

                $excel->setMeta(
                    [
                        'templates' => $taskDTO->template ?: null,
                        'titles' => $taskDTO->title ?: '',
                        'summaries' => $taskDTO->summary ?: '',
                        'headers' => $taskDTO->header ?: '',
                        'footers' => $taskDTO->footer ?: '',
                        'headers_align' => $taskDTO->headerAlign ?: 'right',
                        'footers_align' => $taskDTO->footerAlign ?: 'right',
                        'default_width' => $taskDTO->defaultWidth,
                        'default_height' => $taskDTO->defaultHeight,
                    ]
                );
                return $excel;
            default:
                throw new Exception("不支持的文件类型", ErrCode::FILE_TYPE_ERR);
        }
    }
}
