<?php

namespace App\Http\Controllers\V1;

use App\Domain\Processor\TaskManager;
use App\Domain\Task\ITaskRepository;
use App\Domain\Task\TaskJob;
use App\Domain\Task\TaskService;
use App\ErrCode;
use App\Foundation\DTO\TaskDTO;
use App\Foundation\Queue\Queue;
use EasySwoole\Queue\Job;
use WecarSwoole\Container;
use WecarSwoole\Http\Controller;

class Task extends Controller
{
    protected function validateRules(): array
    {
        return [
            'deliver' => [
                'source_url' => ['required', 'url', 'lengthMin' => 2, 'lengthMax' => 5000],
                'name' => ['required', 'lengthMin' => 2, 'lengthMax' => 60],
                'project_id' => ['required', 'lengthMax' => 40],
                'file_name' => ['lengthMax' => 120],
                'type' => ['inArray' => [null, 'csv', 'excel']],
                'callback' => ['lengthMax' => 300],
                'operator_id' => ['lengthMax' => 120],
                'template' => ['lengthMax' => 8000],
                'title' => ['lengthMax' => 200],
                'summary' => ['lengthMax' => 8000],
            ],
            'one' => [
                'task_id' => ['required', 'lengthMax' => 100],
            ],
            'list' => [
                'project_id' => ['required', 'lengthMax' => 100],
                'page' => ['integer', 'min' => 0],
                'page_size' => ['integer', 'max' => 50],
            ],
        ];
    }

    /**
     * 投递任务
     * 插入数据库 -> 投递到队列中
     */
    public function deliver()
    {
        // 创建新任务
        $task = Container::get(TaskService::class)->create(new TaskDTO($this->params()));
        // 投递
        TaskManager::getInstance()->deliver($task);

        $this->return(['task_id' => $task->id()]);
    }

    /**
     * 查询某个任务详情
     */
    public function one()
    {
        if (!$taskDTO = Container::get(ITaskRepository::class)->getTaskDTOById($this->params('task_id'))) {
            return $this->return([], ErrCode::TASK_NOT_EXISTS, '任务不存在');
        }

        return $this->return($taskDTO->toArray(true, true, false, ['sourceUrl', 'fileName', 'callback', 'template', 'title', 'summary']));
    }

    /**
     * 查询某项目下的任务列表
     */
    public function list()
    {
        $data = Container::get(ITaskRepository::class)->getTaskDTOsByProjId(
            $this->params('project_id'),
            $this->params('page'),
            $this->params('page_size'),
            $this->params('status') ?: 0,
        );

        if (!$data['total']) {
            return $this->return($data);
        }

        $data['data'] = array_map(function (TaskDTO $taskDTO) {
            return $taskDTO->toArray(true, true, false, ['sourceUrl', 'fileName', 'callback', 'template', 'title', 'summary']);
        }, $data['data']);

        return $this->return($data);
    }

    public function test()
    {
        // 标准二维数组
        $a = [
            [
                'wx_micropay' => 130,
                'wx_pay' => 150,
                'ali_micropay' => 100,
                'ali_pay' => 200,
            ],
            [
                'wx_micropay' => 30,
                'wx_pay' => 23,
                'ali_micropay' => 111,
                'ali_pay' => 56,
            ],
        ];

        // 三维数组
        $b = [
            'self_screen' => [
                [
                    'wx_micropay' => 130,
                    'wx_pay' => 150,
                    'ali_micropay' => 100,
                    'ali_pay' => 200,
                ],
                [
                    'wx_micropay' => '20%',
                    'wx_pay' => '10%',
                    'ali_micropay' => '30%',
                    'ali_pay' => '40%',
                ],
            ],
            'pos' => [
                [
                    'wx_micropay' => 130,
                    'wx_pay' => 150,
                    'ali_micropay' => 100,
                    'ali_pay' => 200,
                ],
                [
                    'wx_micropay' => '20%',
                    'wx_pay' => '10%',
                    'ali_micropay' => '30%',
                    'ali_pay' => '40%',
                ],
            ],
        ];
    }
}
