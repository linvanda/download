<?php

namespace App\Domain\Task;

use App\Domain\Project\IProjectRepository;
use App\ErrCode;
use App\Foundation\DTO\TaskDTO;
use WecarSwoole\Container;
use WecarSwoole\Exceptions\Exception;

/**
 * 任务服务
 */
class TaskService
{
    protected $taskRepository;

    public function __construct(ITaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * 创建新任务
     * @return string 任务 id
     */
    public function create(TaskDTO $taskDTO): Task
    {
        $task = Task::buildTask($taskDTO, Container::get(IProjectRepository::class));

        // 存储到数据库
        $this->taskRepository->addTask($task);

        return $task;
    }

    /**
     * 切换任务状态
     */
    public function switchStatus(string $taskId, int $newStatus)
    {
        if (!$task = $this->taskRepository->getTaskById($taskId)) {
            throw new Exception("任务不存在：{$taskId}", ErrCode::TASK_NOT_EXISTS);
        }

        $oldStatus = $task->status();
        $task->switchStatus($newStatus);
        
        if (!$this->taskRepository->changeTaskStatus($taskId, $newStatus, $oldStatus)) {
            throw new Exception("修改任务状态失败：存储失败。{$taskId}：{$oldStatus} -> {$newStatus}", ErrCode::INVALID_STATUS_OP);
        }
    }
}
