<?php

namespace App\Foundation\Repository\Project;

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

        $extra = ['template' => $task->objectFile()->template()];
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

        $extra = $info['extra'] ? json_decode($info['extra'], true) : [];

        $taskDTO = new TaskDTO(
            array_merge(
                $info,
                [
                    'template' => $extra['template'],
                    'title' => $extra['title'] ?? '',
                    'summary' => $extra['summary'] ?? '',
                ]
            )
        );

        $task = Task::buildTask($taskDTO, Container::get(IProjectRepository::class));

        // 这些属性需要单独设置
        $task->id = $info['id'];
        $task->createTime = $info['ctime'];
        $task->lastExecTime = $info['etime'];
        $task->finishedTime = $info['ftime'];
        $task->status = $info['status'];
        $task->execNum = $info['exec_num'];

        return $task;
    }
}
