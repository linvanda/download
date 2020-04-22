<?php

namespace App\Foundation\Repository\Task;

use App\Domain\Project\IProjectRepository;
use App\Domain\Task\Excel;
use WecarSwoole\Repository\MySQLRepository;
use App\Domain\Task\ITaskRepository;
use App\Domain\Task\Task;
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
            'source_url' => $task->source()->uri()->url(),
            'type' => self::FILE_TYPE_MAP[$task->objectFile()->type()],
            'file_name' => $task->objectFile()->fileName(),
            'operator_id' => $task->operator,
            'callback' => $task->callbackURI()->url(),
            'step' => $task->source()->step(),
            'status' => $task->status,
            'exec_num' => $task->execNum,
            'ctime' => $task->createTime,
            'etime' => $task->lastExecTime,
            'ftime' => $task->finishedTime,
        ];

        $extra = [];
        if ($task->objectFile()->template()) {
            $extra['template'] = $task->objectFile()->template();
        }
        $objectFile = $task->objectFile();
        if ($objectFile instanceof Excel) {
            $extra['title'] = $objectFile->title();
            $extra['summary'] = $objectFile->summary();
        }

        $info['extra'] = json_encode($extra);

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

        $task = Task::buildTask($this->buildTaskDTO($info), Container::get(IProjectRepository::class));

        return $task;
    }

    public function getTaskDTOById(string $id): ?TaskDTO
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

    protected function buildTaskDTO(array $info): ?TaskDTO
    {
        $extra = $info['extra'] ? json_decode($info['extra'], true) : [];

        $taskDTO = new TaskDTO(
            array_merge(
                $info,
                [
                    'template' => $extra['template'] ?? [],
                    'title' => $extra['title'] ?? '',
                    'summary' => $extra['summary'] ?? '',
                    'type' => array_flip(self::FILE_TYPE_MAP)[$info['type']],
                ]
            )
        );

        return $taskDTO;
    }
}