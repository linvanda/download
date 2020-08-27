<?php

namespace App\Domain\Source;

use App\Foundation\File\LocalFile;
use App\Domain\URI;
use App\ErrCode;
use App\Exceptions\SourceException;
use App\Foundation\Client\API;
use WecarSwoole\Util\File;
use WecarSwoole\Util\GetterSetter;

/**
 * CSV 数据源，将源数据存储为 CSV 格式
 */
class CSVSource implements ISource
{
    use GetterSetter;
    
    public const STEP_MIN = 100;
    public const STEP_MAX = 5000;
    public const STEP_DEFAULT = 1000;
    public const SOURCE_FNAME = 'source.csv';
    public const EXT_FIELD = '_row_head_';

    protected $uri;
    protected $data;
    protected $step;

    // 生成的本地文件名
    private $fileName;
    // 数据记录数（行数）
    private $count;
    // 源文件大小
    private $size;
    private $taskId;
    private $localFiles = [];

    /**
     * @param URI $uri 数据源 url
     * @param array $data 源数据。data 和 url 至少有一个，以 data 优先（即有 data 则不再从 url 拉取数据）
     * @param string $dir 本地文件存储基路径
     * @param string $taskId 关联的任务编号。此处存储 taskId 而不是 Task 主要避免循环依赖
     * @param int $step 取数步长（每页取多少）
     */
    public function __construct(URI $uri, array $data, string $dir, string $taskId, int $step = self::STEP_DEFAULT)
    {
        if (!$uri->url() && !$data) {
            throw new \Exception("source error:source_url and data are both empty", ErrCode::PARAM_VALIDATE_FAIL);
        }

        $this->uri = $uri;
        $this->setData($data);
        $this->setStep($step);
        $this->setFileName($dir);
        $this->taskId = $taskId;
    }

    /**
     * 源文件名称（包含目录）
     */
    public function fileName(): string
    {
        return $this->fileName;
    }

    /**
     * 数据记录数（行数）
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * 源文件大小，单位字节
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * 从源拉取数据并保存到本地
     * @param API $invoker 源数据调用程序
     */
    public function fetch(API $invoker)
    {
        $cnt = 0;
        $size = 0;
        
        if ($this->data) {
            // 投递任务时提供了 data
            list($cnt, $size) = $this->saveToFile($this->data, true);
        } else {
            // 循环拉取数据
            $page = $n = $total = 0;
            $invoker->setUrl($this->uri->url());

            while ($n++ < 100000) {
                $result = $this->invokeData($invoker, $page, $this->step);
    
                if (!isset($result['data']) || !$data = $result['data']) {
                    break;
                }

                // 保存到文件
                list($tCnt, $tSize) = $this->saveToFile($data, $n == 1 && count($data));
                $cnt += $tCnt;
                $size += $tSize;

                if ($n == 1) {
                    $total = $result['total'] ?? PHP_INT_MAX;// 如果没有提供 total，则会不停地循环拉数据直到拉完
                }
    
                // 为了健壮性，此处做了两方面的检测，防止对方接口有 bug 导致一直拉取数据
                if (count($data) < $this->step || $cnt >= $total) {
                    break;
                }
                $page++;
            }
        }

        $this->closeFiles();

        $this->count = $cnt;
        $this->size = $size;
    }

    /**
     * 从源拉取元数据
     */
    public function fetchMeta(API $invoker): array
    {
        if ($this->data) {
            return ['data' => $this->data];
        }

        $invoker->setUrl($this->uri->url());
        return $this->invokeData($invoker, 0, 1);
    }

    /**
     * 将数据存储到本地文件系统
     * @param array $data 三维数组，第一维表示源文件
     * @return array [保存的行数（不包括标题行）, 文件大小]
     */
    private function saveToFile(array $data, bool $saveFields): array
    {
        if (!$data = $this->formatSourceData($data)) {
            return 0;
        }

        $cnt = $size = 0;
        foreach ($data as $index => $sData) {
            $file = $this->file($index);

            // 将 key 写入，同时写入每列的类型（目前仅支持 number、string 两种类型）
            // 存入格式：field|type，如 age|number,uname|string
            if ($saveFields) {
                $fields = [];
                foreach ($sData[0] as $field => $value) {
                    $fields[] = $field . '|' . (is_int($value) || is_float($value) ? 'number' : 'string');
                }
                $file->saveAsCsv($fields);
            }

            // 存储数据
            $file->saveAsCsv($sData);

            $cnt += count($sData);
            $size += $file->size();
        }

        return [$cnt, $size];
    }

    private function file(int $index): LocalFile
    {
        if (!isset($this->localFiles[$index])) {
            $this->localFiles[$index] = new LocalFile($this->fileName());
        }

        return $this->localFiles[$index];
    }

    private function closeFiles()
    {
        foreach ($this->localFiles as $file) {
            if ($file instanceof LocalFile) {
                $file->close();
            }
        }

        $this->localFiles = [];
    }

    /**
     * 格式化源数据数组格式，统一整理成三维数组，并将 excel 行表头纳入其中
     * // 第一维表示第一个源文件的数据（支持多源模式）
     * [
     *      // 第二维表示每行数据
     *      [
     *          ['name' => '张三', 'age' => 18, ...],
     *      ],
     * ]
     * 
     * @param array $data 原始数据数组
     *      原始数组有以下几种格式（最多四维）：
     *          单源二维数组：
     *              [
     *                  ['name'=>'张三', 'age'=> 18],
     *              ]
     *          单源三维数组（行列表头格式）：
     *              [
     *                  'row_head_one' => [
     *                      ['name'=>'张三', 'age'=> 18],
     *                  ]
     *              ]
     *          多源数组：
     *              情况一：
     *              [
     *                  [
     *                      ['name'=>'张三', 'age'=> 18],
     *                  ]
     *              ]
     *              情况二：
     *              [
     *                  [
     *                      'row_head_one' => [
     *                          ['name'=>'张三', 'age'=> 18],
     *                      ]
     *                  ]
     *              ]
     * @return 格式化后的三维数组
     */
    private function formatSourceData(array $data): array
    {
        if (!$data) {
            return [];
        }

        $firstEle = reset($data);

        /**
         * 单源二维数组
         */
        if (!$firstEle || !is_array($firstEle)) {
            return [];
        }

        // 判断第二维数组的第一个元素
        if (!is_array(reset($firstEle))) {
            return [$data];
        }

        /**
         * 单源三维数组
         */
        if (!is_int(key($data))) {
            $newData = [];
            foreach ($data as $rowHead => $item) {
                foreach ($item as $subItem) {
                    $subItem[self::EXT_FIELD] = $rowHead;
                    $newData[] = $subItem;
                }
            }

            return [$newData];
        }

        /**
         * 多源数组
         */

        // 情况一
        if (is_int(key($firstEle))) {
            return $data;
        }

        // 情况二
        foreach ($data as $k => $v) {
            foreach ($v as $kk => &$vv) {
                foreach ($vv as &$vvv) {
                    $vvv[self::EXT_FIELD] = $kk;
                }
            }

            $data[$k] = array_values($v);
        }
        return $data;
    }

    private function invokeData(API $invoker, int $page, int $pageSize): array
    {
        $result = $invoker->invoke(['page' => $page, 'page_size' => $pageSize, '_task_id' => $this->taskId]);
         
        if (!$result || !isset($result['status']) || $result['status'] !== 200) {
            throw new SourceException(
                "获取源数据失败：返回：" . print_r($result, true),
                ErrCode::FETCH_SOURCE_FAILED
            );
        }

        if (!isset($result['data']['data']) || !isset($result['data']['total'])) {
            throw new SourceException(
                "获取源数据失败：数据格式错误：" . print_r($result, true),
                ErrCode::FETCH_SOURCE_FAILED
            );
        }

        return $result['data'];
    }

    private function setData(array $data)
    {
        if (!$data) {
            return;
        }

        // 此处做一下弱校验
        $firstEle = reset($data);
        if (count($data) > 10000 || is_array($firstEle) && count($firstEle) > 10000) {
            throw new \Exception("static data too large", ErrCode::FETCH_SOURCE_FAILED);
        }

        $this->data = $data;
    }

    private function setFileName(string $dir)
    {
        $this->fileName = File::join($dir, self::SOURCE_FNAME);
    }

    private function setStep(int $step)
    {
        if ($step < self::STEP_MIN || $step > self::STEP_MAX) {
            $step = self::STEP_DEFAULT;
        }

        $this->step = $step;
    }
}
