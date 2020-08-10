<?php

namespace App\Foundation\Repository\Task;

use App\Domain\Project\IProjectRepository;
use WecarSwoole\Repository\MySQLRepository;
use App\Domain\Task\ITaskRepository;
use App\Domain\Task\Merchant;
use App\Domain\Task\Task;
use App\Domain\Task\TaskFactory;
use App\Foundation\DTO\DBTaskDTO;
use Exception;
use WecarSwoole\Container;

class MySQLTaskRepository extends MySQLRepository implements ITaskRepository
{
    protected function dbAlias(): string
    {
        return 'download';
    }

    protected const FILE_TYPE_MAP = ['csv' => 1, 'excel' => 2];

    public function addTask(Task $task)
    {
        $info = [
            'id' => $task->id(),
            'name' => $task->name(),
            'project_id' => $task->project()->id(),
            'source_url' => $task->source()->uri->url(),
            'type' => self::FILE_TYPE_MAP[$task->target()->type()],
            'file_name' => $task->target()->downloadFileName(),
            'operator_id' => $task->operator,
            'merchant_id' => $task->merchant->id(),
            'merchant_type' => $task->merchant->type(),
            'callback' => $task->callbackURI()->url(),
            'step' => $task->source()->step,
            'max_exec_time' => $task->maxExecTime(),
            'status' => $task->status,
            'retry_num' => $task->retryNum,
            'is_sync' => $task->isSync,
            'ctime' => $task->createTime,
            'utime' => $task->createTime,
            'etime' => $task->lastExecTime,
            'ftime' => $task->finishedTime,
            'stime' => $task->lastChangeStatusTime,
            'qtime' => $task->lastEnqueueTime,
        ];

        $info['obj_meta'] = serialize($task->target()->getMeta());

        $this->query->insert('task')->values($info)->execute();

        if (!$this->query->affectedRows()) {
            throw new Exception("存储 task 失败");
        }
    }

    public function getTaskById(string $id): ?Task
    {
        if (!$info = $this->query->select('*')->from('task')->where(['id' => $id, 'is_deleted' => 0])->one()) {
            return null;
        }

        return TaskFactory::create($this->buildTaskDTO($info), Container::get(IProjectRepository::class));
    }

    public function getTaskDTOById(string $id): ?DBTaskDTO
    {
        if (!$info = $this->query->select('*')->from('task')->where(['id' => $id, 'is_deleted' => 0])->one()) {
            return null;
        }

        return $this->buildTaskDTO($info);
    }

    /**
     * 目前只查询异步任务列表
     */
    public function getTaskDTOs(
        array $projectIds,
        int $page,
        int $pageSize = 20,
        array $status = [],
        $operatorId = '',
        Merchant $merchant = null,
        $taskName = ''
    ): Array {
        // 安全起见，一次最多允许查询 200 个
        $pageSize = $pageSize > 200 ? 200 : $pageSize;

        $builder = $this->query
        ->select('*')
        ->from('task')
        ->where(['project_id' => array_filter($projectIds), 'is_deleted' => 0, 'is_sync' => 0])
        ->orderBy("incr_id desc")
        ->limit($pageSize, $page * $pageSize);

        if ($status) {
            $builder->where(['status' => array_filter($status)]);
        }

        if ($operatorId) {
            $builder->where(['operator_id' => $operatorId]);
        }

        if ($merchant) {
            $builder->where(['merchant_id' => $merchant->id(), 'merchant_type' => $merchant->type()]);
        }

        if ($taskName) {
            $builder->where("name like :task_name", ['task_name' => "%{$taskName}%"]);
        }

        $list = $builder->page();
        if (!$list['total']) {
            return $list;
        }

        // 转成 DTO
        $list['data'] = array_map(function (array $item) {
            return $this->buildTaskDTO($item);
        }, $list['data']);


        return $list;
    }

    /**
     * 查询可能需要重试的任务列表
     * @param array $status 状态列表
     * @param int $startTime 任务创建时间起始
     * @param int $endTime 任务创建时间结束
     * @return array DBTaskDTO 对象数组
     */
    public function getTaskDTOsToRetry(array $status, int $startTime, int $endTime): Array
    {
        $list = $this->query
        ->select('*')
        ->from('task')
        ->where(['status' => $status, 'is_deleted' => 0, 'is_sync' => 0])
        ->where("ctime>=:s_ctime and ctime<=:e_ctime", ['s_ctime' => $startTime, 'e_ctime' => $endTime])
        ->list();

        return array_map(function (array $item) {
            return $this->buildTaskDTO($item);
        }, $list);
    }

    public function changeTaskStatus(Task $task, int $oldStatus): bool
    {
        // 注意 where 条件要加上 old status 判断，做乐观锁控制（防止并发修改导致数据错误）
        $this->query
        ->update('task')
        ->set([
            'status' => $task->status(),
            'failed_reason' => $task->failedReason,
            'utime' => time(),
            'etime' => $task->lastExecTime,
            'ftime' => $task->finishedTime,
            'stime' => $task->lastChangeStatusTime,
            'qtime' => $task->lastEnqueueTime,
            'retry_num' => $task->retryNum,
        ])
        ->where(['id' => $task->id(), 'status' => $oldStatus])
        ->execute();

        return $this->query->affectedRows() > 0;
    }

    public function fileTask(int $beforeTime, bool $optimize)
    {
        $this->query->execute('insert into task_history select * from task where ctime<:time', ['time' => $beforeTime]);
        $this->query->execute('delete from task where ctime<:time', ['time' => $beforeTime]);

        if ($optimize) {
            $this->query->execute("optimize table task");
        }
    }

    public function getTaskStatus(string $taskId): int
    {
        return $this->query->select('status')->from('task')->where(['id' => $taskId])->column() ?: 0;
    }

    public function delete(array $taskIds, array $projectIds, $operatorId = '')
    {
        if (!$taskIds || !$projectIds) {
            return;
        }

        $builder = $this->query
        ->update('task')
        ->set(['is_deleted' => 1, 'utime' => time()])
        ->where(['id' => $taskIds, 'project_id' => $projectIds]);

        if ($operatorId) {
            $builder->where(['operator_id' => $operatorId]);
        }

        $builder->execute();
    }

    protected function buildTaskDTO(array $info): ?DBTaskDTO
    {
        $meta = isset($info['obj_meta']) ? unserialize($info['obj_meta']) : [];

        $taskDTO = new DBTaskDTO(
            array_merge(
                $info,
                [
                    'template' => $meta['template'] ?? null,
                    'title' => $meta['title'] ?? '',
                    'summary' => $meta['summary'] ?? '',
                    'header' => $meta['header'],
                    'footer' => $meta['footer'],
                    'type' => array_flip(self::FILE_TYPE_MAP)[$info['type']],
                    'default_width' => $meta['default_width'] ?? 0,
                    'default_height' => $meta['default_height'] ?? 0,
                ]
            )
        );

        return $taskDTO;
    }
}
