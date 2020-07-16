<?php

namespace App\Domain\Task;

use App\Domain\Target\CSV;
use App\Domain\Target\Excel;
use App\Domain\Target\Target;
use App\Domain\Project\IProjectRepository;
use App\Domain\Source\Source;
use App\Domain\URI;
use App\ErrCode;
use App\Foundation\DTO\TaskDTO;
use EasySwoole\EasySwoole\Config;
use WecarSwoole\Container;
use WecarSwoole\Exceptions\Exception;
use WecarSwoole\ID\IIDGenerator;
use WecarSwoole\Util\File;

/**
 * 工厂：创建 task 对象
 */
class TaskFactory
{
    public static function create(TaskDTO $taskDTO, IProjectRepository $projectRepository, IIDGenerator $idGenerator = null): Task
    {
        if (!$project = $projectRepository->getProjectById($taskDTO->projectId)) {
            throw new Exception("创建任务失败：项目不存在", ErrCode::PROJ_NOT_EXISTS);
        }

        // 此处并没有对外界传入的 id 做唯一性校验，有调用方保证
        $idGen = $idGenerator ?: Container::get(IIDGenerator::class);
        $id = $taskDTO->id ?? $idGen->id();
        $taskDTO->id = $id;
        
        // 源
        $source = new Source(
            new URI($taskDTO->sourceUrl),
            File::join(Config::getInstance()->getConf('local_file_base_dir'), $id),
            intval($taskDTO->step) ?: Source::STEP_DEFAULT
        );
        // 目标
        $target = self::buildTarget($taskDTO);
        // 回调
        $callback = new URI($taskDTO->callback ?: '');

        // 基于 DTO 创建 Task 对象
        $task = new Task($id, $taskDTO->name, $project, $source, $target, $callback, $taskDTO->operatorId ?: '');
        // 其他属性设置
        $task->createTime = $taskDTO->ctime ?? time();
        $task->lastExecTime = $taskDTO->etime ?? 0;
        $task->finishedTime = $taskDTO->ftime ?? 0;
        $task->lastChangeStatusTime = $taskDTO->stime ?? 0;
        $task->status = $taskDTO->status ?? Task::STATUS_TODO;
        $task->retryNum = $taskDTO->retryNum ?? 0;

        return $task;
    }

    private static function buildTarget(TaskDTO $taskDTO): Target
    {
        $baseDir = File::join(Config::getInstance()->getConf('local_file_base_dir'), $taskDTO->id);
        switch ($taskDTO->type ?: Target::TYPE_CSV) {
            case Target::TYPE_CSV:
                return new CSV($baseDir, $taskDTO->fileName ?: '');
            case Target::TYPE_EXCEL:
                $excel = new Excel($baseDir, $taskDTO->fileName ?: '');
                $excel->setMeta(
                    [
                        'template' => $taskDTO->template ?: null,
                        'title' => $taskDTO->title ?: '',
                        'summary' => $taskDTO->summary ?: '',
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
