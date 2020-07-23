<?php

namespace App\Http\Controllers\V1;

use App\Domain\Transfer\TransferService;
use App\Foundation\DTO\TaskDTO;
use App\Http\Service\DownloadService;
use EasySwoole\EasySwoole\Config;
use WecarSwoole\Container;
use WecarSwoole\Http\Controller;
use WecarSwoole\Util\Url;

class Download extends Controller
{
    protected function validateRules(): array
    {
        return [
            'asyncGetData' => [
                'task_id' => ['required', 'lengthMax' => 100],
            ],
            'getDownloadUrl' => [
                'task_id' => ['required', 'lengthMax' => 100],
            ],
            'getData' => [
                'ticket' => ['required', 'lengthMax' => 100],
            ],
            'syncGetData' => [
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
        ];
    }

    /**
     * 取数据
     * 后端鉴权后下载数据
     * 注意：调用端需要考虑大文件下载情况，此时如果调用端使用诸如 file_get_contents 将数据一次全部放到内存中
     * 可能会导致内存溢出，此时应该采用 curl 的 CURLOPT_WRITEFUNCTION 将数据写入到本地文件中
     */
    public function asyncGetData()
    {
        Container::get(DownloadService::class)->download($this->params('task_id'), $this->response());
    }

    /**
     * 获取临时下载 url
     */
    public function getDownloadUrl()
    {
        $conf = Config::getInstance();
        $url = Url::assemble($conf->getConf('tmp_download_url'), $conf->getConf('base_url'));
        $this->return(['url' => Container::get(TransferService::class)->buildDownloadUrl($this->params('task_id'), $url)]);
    }

    /**
     * 前端通过临时下载 url 下载数据
     * 无需鉴权，该 url 通过 getDownloadUrl 生成
     */
    public function getData()
    {
        Container::get(DownloadService::class)->downloadWithTicket($this->params('ticket'), $this->response());
    }

    /**
     * 同步下载数据
     * 相当于投递任务和取回数据一体化，用于小批量数据下载
     */
    public function syncGetData()
    {
        Container::get(DownloadService::class)->syncDownload(new TaskDTO($this->params()), $this->response());
    }
}
