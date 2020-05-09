<?php

namespace App\Domain\Source;

use App\Domain\Object\ObjectFile;
use App\Domain\Object\Excel;
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

        $info = $data['data'];

        /**
         * 设置元数据
         */
        $objectFile = $this->task->objectFile();
        if (isset($info['template']) && is_array($info['template'])) {
            $objectFile->setTemplate($info['template']);            
        } else {
            // 如果没有设置动态模板，而且之前也没有提供静态模板，则需要计算默认模板
        }

        // 表格
        if ($objectFile->type() === ObjectFile::TYPE_EXCEL && $objectFile instanceof Excel) {
            if (isset($info['header'])) {
                $objectFile->setHeader($info['header']);
            }
    
            if (isset($info['footer'])) {
                $objectFile->setFooter($info['footer']);
            }
        }
    }
}
