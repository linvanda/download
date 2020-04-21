<?php

namespace App\Domain\Task;

use App\Domain\Project\IProjectRepository;
use App\Domain\Project\Project;
use App\ErrCode;
use App\Foundation\DTO\TaskDTO;
use EasySwoole\Utility\Random;
use WecarSwoole\Entity;
use WecarSwoole\Exceptions\Exception;

class Task extends Entity
{
    // 待处理
    public const STATUS_TODO = 1;
    // 处理中
    public const STATUS_DOING = 2;
    // 处理成功
    public const STATUS_SUC = 3;
    // 处理失败
    public const STATUS_FAILED = 4;

    // 任务 id
    protected $id;
    // 任务名称
    protected $name;
    // 所属的项目
    protected $project;
    // 数据源
    protected $source;
    // 目标文件
    protected $objectFile;
    // 回调通知 uri
    protected $callback;
    // 操作者编号
    protected $operator;
    // 任务创建时间
    protected $createTime;
    // 任务最后处理时间
    protected $lastExecTime;
    // 任务完成时间
    protected $finishedTime;
    // 任务状态
    protected $status;
    // 任务执行次数
    protected $execNum;

    public function __construct(
        string $name,
        Project $project,
        Source $source,
        ObjectFile $objectFile,
        URI $callback = null,
        string $operator = ''
    ) {
        $this->id = $this->generateId();
        $this->name = $name;
        $this->project = $project;
        $this->source = $source;
        $this->objectFile = $objectFile;
        $this->callback = $callback;
        $this->operator = $operator;

        $this->createTime = time();
        $this->lastExecTime = 0;
        $this->finishedTime = 0;
        $this->execNum = 0;
        $this->status = self::STATUS_TODO;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function project(): Project
    {
        return $this->project;
    }

    public function source(): Source
    {
        return $this->source;
    }

    public function objectFile(): ObjectFile
    {
        return $this->objectFile;
    }

    public function callbackURI(): URI
    {
        return $this->callback;
    }

    /**
     * 工厂方法：创建 task 对象
     */
    public static function buildTask(TaskDTO $taskDTO, IProjectRepository $projectRepository): Task
    {
        if (!$project = $projectRepository->getProjectById($taskDTO->projectId)) {
            throw new Exception("创建任务失败：项目不存在", ErrCode::PROJ_NOT_EXISTS);
        }

        $source = new Source(new URI($taskDTO->sourceUrl), $taskDTO->step);
        $objectFile = self::buildObjectFile($taskDTO);
        $callback = new URI($taskDTO->callback);

        return new Task($taskDTO->name, $project, $source, $objectFile, $callback, $taskDTO->operatorId);
    }

    protected static function buildObjectFile(TaskDTO $taskDTO): ObjectFile
    {
        switch ($taskDTO->type) {
            case ObjectFile::TYPE_CSV:
                return new CSV($taskDTO->fileName, $taskDTO->template);
            case ObjectFile::TYPE_EXCEL:
                return new Excel($taskDTO->fileName, $taskDTO->template, $taskDTO->title, $taskDTO->summary);
            default:
                throw new Exception("不支持的文件类型", ErrCode::FILE_TYPE_ERR);
        }
    }

    protected function generateId(): string
    {
        return Random::makeUUIDV4();
    }
}
