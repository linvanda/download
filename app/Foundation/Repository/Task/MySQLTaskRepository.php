<?php

namespace App\Foundation\Repository\Task;

use App\Domain\Project\IProjectRepository;
use App\Domain\Target\Target;
use WecarSwoole\Repository\MySQLRepository;
use App\Domain\Task\ITaskRepository;
use App\Domain\Task\Task;
use App\Domain\Task\TaskFactory;
use App\Foundation\DTO\DBTaskDTO;
use App\Foundation\DTO\TaskDTO;
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
            'callback' => $task->callbackURI()->url(),
            'step' => $task->source()->step,
            'max_exec_time' => $task->maxExecTime(),
            'status' => $task->status,
            'retry_num' => 0,
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

        $task = TaskFactory::create($this->buildTaskDTO($info), Container::get(IProjectRepository::class));

        return $task;
    }

    public function getTaskDTOById(string $id): ?DBTaskDTO
    {
        if (!$info = $this->query->select('*')->from('task')->where(['id' => $id, 'is_deleted' => 0])->one()) {
            return null;
        }

        return $this->buildTaskDTO($info);
    }

    public function getTaskDTOsByProjId(string $projectId, int $page, int $pageSize = 20, int $status = 0): Array
    {
        // 安全起见，一次最多允许查询 200 个
        $pageSize = $pageSize > 200 ? 200 : $pageSize;

        $builder = $this->query
        ->select('*')
        ->from('task')->where(['project_id' => $projectId, 'is_deleted' => 0])
        ->orderBy("incr_id desc")->limit($pageSize, $page);

        if ($status) {
            $builder->where(['status' => $status]);
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
                    'type' => array_flip(self::FILE_TYPE_MAP)[$info['type']],
                    'default_width' => $meta['default_width'] ?? 0,
                    'default_height' => $meta['default_height'] ?? 0,
                ]
            )
        );

        return $taskDTO;
    }
}
