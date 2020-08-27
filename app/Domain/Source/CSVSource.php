<?php

namespace App\Domain\Source;

use App\Domain\Target\Target;
use App\Foundation\File\LocalFile;
use App\Domain\Target\Template\Excel\RowHead;
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
     * @param LocalFile $file
     */
    public function fetch(API $invoker, LocalFile $file, string $targetType)
    {
        $cnt = 0;
        
        if ($this->data) {
            // 投递任务时提供了 data
            $cnt += $this->saveToFile($file, $this->data, $targetType, true);
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
                $cnt += $this->saveToFile($file, $data, $targetType, $n == 1 && count($data));

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

        $file->close();

        $this->count = $cnt;
        $this->size = $file->size();
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
     * @return int 保存的行数（不包括标题行）
     */
    private function saveToFile(LocalFile $file, array $data, string $targetType, bool $saveFields): int
    {
        $data = $this->formatSourceData($data, $targetType);

        // 将 key 写入，同时写入每列的类型（目前仅支持 number、string 两种类型）
        // 存入格式：field|type，如 age|number,uname|string
        if ($saveFields) {
            $fields = [];
            foreach ($data[0] as $field => $value) {
                $fields[] = $field . '|' . (is_int($value) || is_float($value) ? 'number' : 'string');
            }
            $file->saveAsCsv($fields);
        }

        // 存储数据
        $file->saveAsCsv($data);

        return count($data);
    }

    /**
     * 格式化源数据数组格式，统一整理成二维数组，并将行表头纳入其中
     */
    private function formatSourceData(array $data, $targetType): array
    {
        if (!$data) {
            return $data;
        }

        $firstVal = $data[0] ?? array_values($data)[0] ?? [];

        if (!is_array(array_values($firstVal)[0])) {
            if ($targetType == Target::TYPE_EXCEL) {
                // 加上默认的 row_head
                return array_map(function ($item) {
                    if (!isset($item[RowHead::NODE_ROW_HEADER_COL])) {
                        $item[RowHead::NODE_ROW_HEADER_COL] = '';
                    }
                    return $item;
                }, $data);
            } else {
                return $data;
            }
        }

        // 三维转二维
        $newData = [];
        foreach ($data as $rowHead => $item) {
            foreach ($item as $subItem) {
                if ($targetType == Target::TYPE_EXCEL) {
                    $subItem[RowHead::NODE_ROW_HEADER_COL] = $rowHead;
                }
                $newData[] = $subItem;
            }
        }

        return $newData;
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

        if (count($data) > 20000) {
            throw new \Exception("static data can not large than 20000", ErrCode::FETCH_SOURCE_FAILED);
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
