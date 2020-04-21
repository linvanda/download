<?php

namespace App\Http\Controllers\V1;

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
                'type' => ['inArray' => ['csv', 'excel']],
                'callback' => ['url', 'lengthMax' => 300],
                'step' => ['integer', 'between' => [100, 1000]],
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
            ],
        ];
    }

    /**
     * 投递任务
     * 插入数据库 -> 投递到队列中
     */
    public function deliver()
    {

    }

    /**
     * 查询某个任务详情
     */
    public function one()
    {

    }

    /**
     * 查询某项目下的任务列表
     */
    public function list()
    {

    }
}
