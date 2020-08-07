<?php

namespace App\Http\Controllers\V1;

use App\Domain\Task\ITaskRepository;
use App\Domain\Task\Merchant;
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
                'merchant_id' => ['required'],
                'merchant_type' => ['required'],
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
            'delete' => [
                'task_ids' => ['required', 'lengthMax' => 50000],
                'project_ids' => ['required', 'lengthMax' => 1000],
            ]
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
        return $this->return($this->formateTask($taskArr));
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
            $this->params('merchant_id') !== null ? new Merchant($this->params('merchant_id'), $this->params('merchant_type')) : null,
            $this->params('task_name') ?: ''
        );

        if (!$data['total']) {
            return $this->return($data);
        }

        $data['data'] = array_map(function (TaskDTO $taskDTO) {
            $taskArr = $taskDTO->toArray(true, true, false, ['sourceUrl', 'fileName', 'callback', 'template', 'title', 'summary', 'header', 'footer']);
            return $this->formateTask($taskArr);
        }, $data['data']);

        return $this->return($data);
    }

    /**
     * 删除任务
     */
    public function delete()
    {
        Container::get(ITaskRepository::class)->delete(explode(',', $this->params('task_ids')), explode(',', $this->params('project_ids')));
        return $this->return();
    }

    private function formateTask(array $task): array
    {
        // 创建时间超过 7 天的认为已过期，不可下载
        if (time() - $task['ctime'] >= 86400 * 7) {
            $task['status'] = DlTask::STATUS_EXPIRED;
        }

        // 状态处理
        $task['status'] = [
            DlTask::STATUS_TODO => DlTask::STATUS_DOING,
            DlTask::STATUS_ENQUEUED => DlTask::STATUS_DOING,
            DlTask::STATUS_DOING => DlTask::STATUS_DOING,
            DlTask::STATUS_FAILED => DlTask::STATUS_DOING,
            DlTask::STATUS_SUC => DlTask::STATUS_SUC,
            DlTask::STATUS_ERR => DlTask::STATUS_ERR,
            DlTask::STATUS_EXPIRED => DlTask::STATUS_EXPIRED,
        ][$task['status']];
        $task['status_name'] = [
            DlTask::STATUS_DOING => '处理中',
            DlTask::STATUS_SUC => '处理成功',
            DlTask::STATUS_ERR => '处理失败',
            DlTask::STATUS_EXPIRED => '已过期',
        ][$task['status']];

        return $task;
    }
}
