<?php

namespace App\Http\Controllers\V1;

use App\Domain\Task\ITaskRepository;
use App\Domain\Task\Task as DlTask;
use App\Domain\Task\TaskService;
use App\ErrCode;
use App\Foundation\DTO\TaskDTO;
use App\Processor\TaskManager;
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
                'project_ids' => ['required', 'lengthMax' => 1000],
                'page' => ['required', 'integer', 'min' => 0],
                'page_size' => ['optional', 'integer', 'max' => 100],
            ],
        ];
    }

    /**
     * 投递任务
     */
    public function deliver()
    {
        $task = Container::get(TaskService::class)->create(new TaskDTO($this->params()));
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

        $taskArr = $taskDTO->toArray(true, true, false, ['sourceUrl', 'fileName', 'callback', 'template', 'title', 'summary', 'header', 'footer']);
        // 状态处理
        $taskArr['status'] = [
            DlTask::STATUS_TODO => DlTask::STATUS_DOING,
            DlTask::STATUS_ENQUEUED => DlTask::STATUS_DOING,
            DlTask::STATUS_DOING => DlTask::STATUS_DOING,
            DlTask::STATUS_FAILED => DlTask::STATUS_DOING,
            DlTask::STATUS_SUC => DlTask::STATUS_SUC,
            DlTask::STATUS_ERR => DlTask::STATUS_ERR,
        ][$taskArr['status']];
        $taskArr['status_name'] = [
            DlTask::STATUS_DOING => '处理中',
            DlTask::STATUS_SUC => '处理成功',
            DlTask::STATUS_ERR => '处理失败',
        ][$taskArr['status']];

        return $this->return();
    }

    /**
     * 查询任务列表
     */
    public function list()
    {
        $data = Container::get(ITaskRepository::class)->getTaskDTOs(
            explode(',', $this->params('project_ids')),
            intval($this->params('page')),
            $this->params('page_size') ? intval($this->params('page_size')) : 20,
            $this->params('status') ? explode(',', $this->params('status')) : [],
            $this->params('operator_id') ?: '',
            $this->params('task_name') ?: ''
        );

        if (!$data['total']) {
            return $this->return($data);
        }

        $data['data'] = array_map(function (TaskDTO $taskDTO) {
            $taskArr = $taskDTO->toArray(true, true, false, ['sourceUrl', 'fileName', 'callback', 'template', 'title', 'summary', 'header', 'footer']);
            // 状态处理
            $taskArr['status'] = [
                DlTask::STATUS_TODO => DlTask::STATUS_DOING,
                DlTask::STATUS_ENQUEUED => DlTask::STATUS_DOING,
                DlTask::STATUS_DOING => DlTask::STATUS_DOING,
                DlTask::STATUS_FAILED => DlTask::STATUS_DOING,
                DlTask::STATUS_SUC => DlTask::STATUS_SUC,
                DlTask::STATUS_ERR => DlTask::STATUS_ERR,
            ][$taskArr['status']];
            $taskArr['status_name'] = [
                DlTask::STATUS_DOING => '处理中',
                DlTask::STATUS_SUC => '处理成功',
                DlTask::STATUS_ERR => '处理失败',
            ][$taskArr['status']];

            return $taskArr;
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

        // 三维数组，注意：三维数组时，total是第三维数据数量
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
            'other' => [
                'wx_micropay' => '20%',
                'wx_pay' => '10%',
                'ali_micropay' => '30%',
                'ali_pay' => '40%',
            ],
        ];

        // 三维数组也可以用二维表示法：
        $bb = [
            [
                '_row_head_' => 'self_screen',
                'wx_micropay' => 130,
                'wx_pay' => 150,
                'ali_micropay' => 100,
                'ali_pay' => 200,
            ],
            [
                '_row_head_' => 'self_screen',
                'wx_micropay' => '20%',
                'wx_pay' => '10%',
                'ali_micropay' => '30%',
                'ali_pay' => '40%',
            ],
            [
                '_row_head_' => 'pos',
                'wx_micropay' => 130,
                'wx_pay' => 150,
                'ali_micropay' => 100,
                'ali_pay' => 200,
            ],
        ];

        // 没有行表头的二维数组是三维的一种特殊形式，其 _row_head_ 为🈳️字符串：
        $aa = [
            [
                '_row_head_' => '',
                'wx_micropay' => 130,
                'wx_pay' => 150,
                'ali_micropay' => 100,
                'ali_pay' => 200,
            ],
            [
                '_row_head_' => '',
                'wx_micropay' => 30,
                'wx_pay' => 23,
                'ali_micropay' => 111,
                'ali_pay' => 56,
            ],
        ];
    }
}
