<?php

namespace App\Http\Controllers\V1;

use WecarSwoole\Http\Controller;

class Download extends Controller
{
    protected function validateRules(): array
    {
        return [
            'retreive' => [
                'task_id' => ['required', 'lengthMax' => 100],
                'type' => ['inArray' => ['redirect', 'download']],
            ],
            'getData' => [
                'ticket' => ['required', 'lengthMax' => 100],
            ],
            'syncGetData' => [
                'source' => ['required', 'url', 'lengthMin' => 2, 'lengthMax' => 5000],
                'project_id' => ['required', 'lengthMax' => 40],
                'file_name' => ['lengthMax' => 120],
                'type' => ['inArray' => ['csv', 'excel']],
                'step' => ['integer', 'between' => [100, 1000]],
                'operator' => ['lengthMax' => 120],
                'template' => ['lengthMax' => 8000],
                'title' => ['lengthMax' => 200],
                'summary' => ['lengthMax' => 8000],
            ],
        ];
    }

    /**
     * 取数据
     */
    public function retreive()
    {

    }

    /**
     * 前端下载数据
     */
    public function getData()
    {

    }

    /**
     * 同步下载数据
     */
    public function syncGetData()
    {

    }
}
