<?php

namespace App\Http\Routes;

use WecarSwoole\Http\ApiRoute;

class AuthAPI extends ApiRoute
{
    public function map()
    {
        /**
         * 创建项目
         *  param:
         *      name string 必填。项目名称。不可重复
         *      group_id string 必填。项目组
         *  return:
         *      project_id string 项目 id
         */
        $this->post('/v1/project', '/V1/Project/createProject');

        /**
         * 创建项目组
         * param:
         *      name string 必填。名称，不可重复
         * return:
         *      group_id string 项目组 id
         */
        $this->post('/v1/group', '/V1/Project/createGroup');
        
        /**
         * 投递任务
         * param:
         *      source_url string 必填。数据源 url
         *      project_id string 必填。项目 id，由下载中心分配
         *      name string 必填。任务名称
         *      file_name string 可选。下载文件的名称，默认根据日期加随机数生成
         *      type string 可选。可选值：csv|excel。默认 csv
         *      callback string 可选。处理完成后回调通知 url
         *      step int 可选。下载数据时每次取多少数据（步长），默认 1000，可设置范围：100 - 5000
         *      operator_id string 可选。操作员编号，存根用
         *      template string 可选。表头格式定义。仅对 excel 生效
         *      title string 可选。表格标题。仅对 excel 生效
         *      summary string 可选。表格摘要。仅对 excel 生效
         *      header  array 可选。表格 header。仅对 excel 生效
         *      footer array 可选。表格 footer。仅对 excel 生效
         *      default_width int 可选。表格列宽度，单位 pt。仅对 excel 生效
         *      default_height int 可选。表格行高度，单位 pt。仅对 excel 生效
         *      max_exec_time int 可选。任务处理时限（超过该时限还在“处理中”的任务将重新入列），单位秒
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
         *      project_id string 必填。项目 id
         *      page int 必填。页码，从 0 开始
         *      page_size int 必填。步长，最多 200
         *      status string 可选。多个状态用英文逗号隔开，默认全部状态
         */
        $this->get('/v1/tasks', '/V1/Task/list');

        /**
         * 删除任务
         * params:
         *      task_ids string 要删除的任务列表，多个用英文逗号隔开
         *      project_ids string 任务所在的项目，多个用英文逗号隔开
         */
        $this->post('/v1/tasks/delete', 'V1/Task/delete');

        /**
         * 取数据（获取异步生成好的数据）
         * params:
         *      task_id string 必填。任务编号
         */
        $this->get('/v1/download/async', '/V1/Download/asyncGetData');

        /**
         * 获取下载临时 url
         * params:
         *      task_id string 必填。任务编号
         */
        $this->get('/v1/download/url', '/V1/Download/getDownloadUrl');

        /**
         * 同步获取数据（即不需要投递任务，而是直接调该接口生成并下载目标文件）
         * 该接口仅支持获取少量数据（具体的数据条数限制取决于配置）
         * 接口参数同 POST /v1/task 的，除了没有其中的关于异步相关的参数（如 callback）
         */
        $this->get('/v1/download/sync', '/V1/Download/syncGetData');
    }
}
