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
     * @param array $status 任务状态列表，空数组表示全部状态
     * @param int $page 分页，从 0 开始
     * @param int $pageSize 每页数
     * @return Array DBTaskDTO 数组
     */
    public function getTaskDTOsByProjId(string $projectId, int $page, int $pageSize = 20, array $status = []): Array;

    /**
     * 查询可能需要重试的任务列表
     * @param array $status 状态列表
     * @param int $startTime 任务创建时间起始
     * @param int $endTime 任务创建时间结束
     * @return array DBTaskDTO 对象数组
     */
    public function getTaskDTOsToRetry(array $status, int $startTime, int $endTime): Array;

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

    /**
     * 查询任务状态
     */
    public function getTaskStatus(string $taskId): int;
}
