<?php

namespace App\Domain\Task;

interface ITaskRepository
{
    public function addTask(Task $task);

    public function getTaskById(string $id): ?Task;
}
