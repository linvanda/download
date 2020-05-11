<?php

namespace App\Domain\Source;

use App\Domain\Object\Excel;
use App\Domain\Object\Obj;
use App\Domain\Object\Template\Excel\TableTpl;
use App\ErrCode;
use App\Foundation\Client\API;
use WecarSwoole\Exceptions\Exception;

/**
 * 动态元数据
 * 该类会修改 $obj 的数据
 */
class MetaData
{
    private $obj;
    private $invoker;

    public function __construct(Obj $obj, API $invoker)
    {
        $this->obj = $obj;
        $this->invoker = $invoker;
    }

    /**
    * 获取动态元数据信息
    * 当文件格式是 excel 时，对方返回的 data 结构：
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
        $meta = [];
        switch ($this->obj->type()) {
            case Obj::TYPE_EXCEL:
                $meta = $this->getExcelMetaData($info['data'] ?? [], $info['template'] ?? [], $info['header'] ?? [], $info['footer'] ?? []);
                break;
        }

        if ($meta) {
            $this->obj->setMeta($meta);
        }
    }

    private function getExcelMetaData(array $data, array $tableTpl = [], array $header = [], array $footer = [])
    {
        $excel = $this->obj;
        if (!$excel instanceof Excel) {
            return;
        }

        $meta = [];

        // 如果没有设置动态模板，而且之前也没有提供静态模板，则需要计算默认模板
        $meta['table_tpl'] = $tableTpl ?: $excel->getMeta('table_tpl') ?: TableTpl::getDefaultTplFromData($data[0]);

        if ($header) {
            $meta['header'] = $header;
        }

        if ($footer) {
            $meta['footer'] = $footer;
        }

        return $meta;
    }
}
