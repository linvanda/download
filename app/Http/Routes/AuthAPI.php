<?php

namespace App\Http\Routes;

use WecarSwoole\Http\ApiRoute;

class AuthAPI extends ApiRoute
{
    public function map()
    {
        /**
         * 投递任务
         * param:
         *      source string 必填。数据源 url
         *      project_id string 必填。项目 id，由下载中心分配
         *      file_name string 可选。下载文件的名称，默认根据日期加随机数生成
         *      type string 可选。可选值：csv|excel。默认 csv
         *      callback string 可选。处理完成后回调通知 url
         *      step int 可选。下载数据时每次取多少数据（步长），默认 500，可设置范围：100 - 1000
         *      operator string 可选。操作员编号，存根用
         *      template string 可选。表头格式定义。仅对 excel 生效
         *      title string 可选。表格标题。仅对 excel 生效
         *      summary string 可选。表格摘要。仅对 excel 生效
         */
        $this->post("/v1/task", "/V1/Task/deliver");

        /**
         * 查询任务详情
         * params:
         *      task_id string 必填。任务编号
         */
        $this->get('/v1/task', '/V1/Task/one');

        /**
         * 根据项目查询任务列表
         * params:
         *      project_id string 必填。项目编号，由下载中心分配
         */
        $this->get('/v1/tasks', '/V1/Task/list');

        /**
         * 取数据
         * params:
         *      task_id string 必填。任务编号
         *      type string 可选。取数据方式。可选值：redirect|download。redirect：接口返回用户下载数据的临时 url；download：接口直接返回数据本身
         */
        $this->get('/v1/retreive', '、V1/Download/retreive');
    }
}
