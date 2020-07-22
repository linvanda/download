<?php

namespace App\Domain\Task;

use App\Foundation\DTO\DBTaskDTO;

interface ITaskRepository
{
    public function addTask(Task $task);

    public function getTaskById(string $id): ?Task;

    /**
     * 根据任务 id 获取任务详情 DTO
     */
    public function getTaskDTOById(string $id): ?DBTaskDTO;

    /**
     * 根据项目 id 获取该项目下的任务列表，按照任务创建时间倒序排列
     * @param string $projectId 项目 id
     * @param int $status 任务状态，0 表示所有
     * @param int $page 分页，从 0 开始
     * @param int $pageSize 每页数
     * @return Array DBTaskDTO 数组
     */
    public function getTaskDTOsByProjId(string $projectId, int $page, int $pageSize = 20, int $status = 0): Array;

    /**
     * 修改任务状态
     * @return bool 是否修改成功
     */
    public function changeTaskStatus(Task $task, int $oldStatus): bool;

    /**
     * 归档 $beforeTime 之前的数据
     * @param int $beforeTime 归档此时间之前的数据（unix timestamp）
     * @param bool $optimize 是否执行 optimize table 整理表碎片
     */
    public function fileTask(int $beforeTime, bool $optimize);
}
