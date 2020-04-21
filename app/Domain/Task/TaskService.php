<?php

namespace App\Domain\Task;

use App\Domain\Project\IProjectRepository;
use App\Foundation\DTO\TaskDTO;
use WecarSwoole\Container;

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
     * 任务投递
     * @return string 任务 id
     */
    public function deliver(TaskDTO $taskDTO): Task
    {
        $task = Task::buildTask($taskDTO, Container::get(IProjectRepository::class));
        $this->taskRepository->addTask($task);

        return $task;
    }
}
