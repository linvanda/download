<?php

namespace App\Domain\Source;

use App\Domain\Object\Excel;
use App\Domain\Object\Obj;
use App\Domain\Object\Template\Excel\TableTpl;
use App\Domain\Task\Task;
use App\ErrCode;
use App\Foundation\Client\API;
use WecarSwoole\Exceptions\Exception;

/**
 * 动态元数据
 * 该类会修改 $task 的数据
 */
class MetaData
{
    private $task;
    private $invoker;

    public function __construct(Task $task, API $invoker)
    {
        $this->task = $task;
        $this->invoker = $invoker;
    }

    /**
    * 获取动态元数据信息
    * 对方返回的 data 结构：
    * {
    *      'status' => 200,
    *      'msg' => '错误信息',
    *      'data' => {
    *          "template": {...}, // 设置表头格式，可选
    *          "total": 10000, // 一共有多少数据，必需
    *          "data":[{...},{...}] // 二维数据列表，必需
    *          "header":{"油站":"钓鱼岛","姓名":"张三"}, // 头部区，一维或二维数组。可选
    *          "footer":{"签名":"","日期":""}, // 页脚区，一维或二维数组。可选
    *      }
    * }
    */
    public function fetch()
    {
        $data = $this->invoker->invoke(['page' => 0, 'page_size' => 1]);

        if (!$data || !isset($data['status']) || $data['status'] !== 200) {
            throw new Exception("获取元数据失败，返回错误：" . print_r($data, true), ErrCode::FETCH_SOURCE_FAILED);
        }

        $this->setMetaData($data['data']);
    }

    /**
     * 设置元数据
     */
    private function setMetaData(array $info)
    {
        switch ($this->task->object()->type()) {
            case Obj::TYPE_EXCEL:
                $this->setExcelMetaData($info['data'] ?? [], $info['template'] ?? [], $info['header'] ?? [], $info['footer'] ?? []);
                break;
        }
    }

    private function setExcelMetaData(array $data, array $tableTpl = [], array $header = [], array $footer = [])
    {
        $object = $this->task->object();
        if (!$object instanceof Excel) {
            return;
        }

        if ($tableTpl) {
            $object->setTableTpl($tableTpl);            
        } elseif ($object->tableTpl() === null && $data) {
            // 如果没有设置动态模板，而且之前也没有提供静态模板，则需要计算默认模板
            $object->setTableTpl(TableTpl::getDefaultTplFromData($data[0]));
        }

        if ($header) {
            $object->setHeader($header);
        }

        if ($footer) {
            $object->setFooter($footer);
        }
    }
}
