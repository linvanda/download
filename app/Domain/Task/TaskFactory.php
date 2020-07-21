<?php

namespace App\Domain\Task;

use App\Domain\Target\CSV;
use App\Domain\Target\Excel;
use App\Domain\Target\Target;
use App\Domain\Project\IProjectRepository;
use App\Domain\Source\Source;
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
        $callback = $taskDTO->callback ? new URI($taskDTO->callback) : null;

        // 基于 DTO 创建 Task 对象
        $task = new Task($id, $taskDTO->name, $project, $source, $target, $callback, $taskDTO->operatorId ?: '');

        if ($taskDTO instanceof DBTaskDTO) {
            // 来自存储层的数据，需要设置其他属性
            $task->createTime = $taskDTO->ctime;
            $task->lastExecTime = $taskDTO->etime;
            $task->finishedTime = $taskDTO->ftime;
            $task->lastChangeStatusTime = $taskDTO->stime;
            $task->status = $taskDTO->status;
            $task->retryNum = $taskDTO->retryNum;
        }

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
