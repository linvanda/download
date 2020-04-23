<?php

namespace App\Domain\Task;

use App\Domain\File\CSV;
use App\Domain\File\Excel;
use App\Domain\File\ObjectFile;
use App\Domain\Project\IProjectRepository;
use App\Domain\Project\Project;
use App\ErrCode;
use App\Foundation\DTO\TaskDTO;
use WecarSwoole\Container;
use WecarSwoole\Entity;
use WecarSwoole\Exceptions\Exception;
use WecarSwoole\ID\IIDGenerator;

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

    /**
     * 外界必须通过工厂方法来创建
     */
    protected function __construct(
        string $id,
        string $name,
        Project $project,
        Source $source,
        ObjectFile $objectFile,
        URI $callback = null,
        string $operator = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->project = $project;
        $this->source = $source;
        $this->objectFile = $objectFile;
        $this->callback = $callback;
        $this->operator = $operator;
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

    public function status(): int
    {
        return $this->status;
    }

    /**
     * 更改任务状态
     */
    public function switchStatus(int $newStatus)
    {
        if (!in_array($newStatus, [self::STATUS_TODO, self::STATUS_DOING, self::STATUS_SUC, self::STATUS_FAILED])) {
            throw new Exception("非法的任务状态：{$newStatus}", ErrCode::INVALID_STATUS_OP);
        }

        if (!$this->isStatusValid($newStatus)) {
            throw new Exception("非法的状态切换：{$this->status} -> {$newStatus}", ErrCode::INVALID_STATUS_OP);
        }

        $this->status = $newStatus;
    }

    /**
     * 工厂方法：创建 task 对象
     */
    public static function buildTask(TaskDTO $taskDTO, IProjectRepository $projectRepository): Task
    {
        if (!$project = $projectRepository->getProjectById($taskDTO->projectId)) {
            throw new Exception("创建任务失败：项目不存在", ErrCode::PROJ_NOT_EXISTS);
        }

        $id = $taskDTO->id ?? Container::get(IIDGenerator::class)->id();
        
        $source = new Source(new URI($taskDTO->sourceUrl), intval($taskDTO->step) ?: Source::STEP_DEFAULT);
        $objectFile = self::buildObjectFile($taskDTO);
        $callback = new URI($taskDTO->callback ?: '');

        // 基于 DTO 创建 Task 对象
        $task = new Task($id, $taskDTO->name, $project, $source, $objectFile, $callback, $taskDTO->operatorId ?: '');
        // 其他属性设置
        $task->createTime = $taskDTO->ctime ?: time();
        $task->lastExecTime = $taskDTO->etime ?: 0;
        $task->finishedTime = $taskDTO->ftime ?: 0;
        $task->status = $taskDTO->status ?: Task::STATUS_TODO;
        $task->execNum = $taskDTO->execNum ?: 0;

        return $task;
    }

    protected static function buildObjectFile(TaskDTO $taskDTO): ObjectFile
    {
        switch ($taskDTO->type ?: ObjectFile::TYPE_CSV) {
            case ObjectFile::TYPE_CSV:
                return new CSV($taskDTO->fileName ?: '', $taskDTO->template ?: null);
            case ObjectFile::TYPE_EXCEL:
                return new Excel($taskDTO->fileName ?: '', $taskDTO->template ?: null, $taskDTO->title ?: '', $taskDTO->summary ?: '');
            default:
                throw new Exception("不支持的文件类型", ErrCode::FILE_TYPE_ERR);
        }
    }

    /**
     * 检测状态切换是否合法
     */
    private function isStatusValid(int $newStatus): bool
    {
        if (
            $newStatus == self::STATUS_TODO && $this->status != self::STATUS_FAILED
            || $newStatus == self::STATUS_DOING && $this->status != self::STATUS_TODO
            || $newStatus == self::STATUS_SUC && $this->status != self::STATUS_DOING
            || $newStatus == self::STATUS_FAILED && $this->status != self::STATUS_DOING
        ) {
                return false;
        }

        return true;
    }
}
