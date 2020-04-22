<?php

namespace App\Domain\Task;

use App\Foundation\DTO\TaskDTO;

interface ITaskRepository
{
    public function addTask(Task $task);

    public function getTaskById(string $id): ?Task;

    /**
     * 根据任务 id 获取任务详情 DTO
     */
    public function getTaskDTOById(string $id): ?TaskDTO;

    /**
     * 根据项目 id 获取该项目下的任务列表，按照任务创建时间倒序排列
     * @param string $projectId 项目 id
     * @param int $status 任务状态，0 表示所有
     * @param int $page 分页，从 0 开始
     * @param int $pageSize 每页数
     * @return Array TaskDTO 数组
     */
    public function getTaskDTOsByProjId(string $projectId, int $page, int $pageSize = 20, int $status = 0): Array;
}
