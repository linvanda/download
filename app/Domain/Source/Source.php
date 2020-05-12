<?php

namespace App\Domain\Source;

use App\Domain\File\LocalFile;
use App\Domain\URI;
use App\ErrCode;
use App\Exceptions\SourceException;
use App\Foundation\Client\API;
use WecarSwoole\Util\File;
use WecarSwoole\Util\GetterSetter;

/**
 * 数据源
 */
class Source
{
    use GetterSetter;
    
    public const STEP_MIN = 100;
    public const STEP_MAX = 1500;
    public const STEP_DEFAULT = 500;

    // 生成的本地文件名
    private $fileName;
    // 数据记录数（行数）
    private $count;
    // 源文件大小
    private $size;

    protected $uri;
    protected $step;

    public function __construct(URI $uri, string $dir, int $step = 500)
    {
        $this->uri = $uri;
        $this->setStep($step);
        $this->setFileName($dir);
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
    public function fetch(API $invoker, LocalFile $file)
    {
        $invoker->setUrl($this->uri->url());
        $page = $n = $cnt = $total = 0;
        
        while ($n++ < 1000000) {
            $result = $this->invokeData($invoker, $page, $this->step);

            $data = $result['data'];
            $cnt += count($data);

            // 第一次获取数据时将 key 写入
            if ($n == 1 && count($data)) {
                $total = $result['total'];
                $file->saveAsCsv(array_keys($data[0]));
            }

            // 存储数据
            $file->saveAsCsv($data);

            // 为了健壮性，此处做了两方面的检测，防止对方接口有 bug 导致一直拉取数据
            if (count($data) < $this->step || $cnt >= $total) {
                break;
            }

            $page++;
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
        $invoker->setUrl($this->uri->url());
        return $this->invokeData($invoker, 0, 1);
    }

    private function invokeData(API $invoker, int $page, int $pageSize): array
    {
        $result = $invoker->invoke(['page' => $page, 'page_size' => $pageSize]);
         
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

    private function setFileName(string $dir)
    {
        $this->fileName = File::join($dir, 'source.csv');
    }

    private function setStep(int $step)
    {
        if ($step < self::STEP_MIN || $step > self::STEP_MAX) {
            $step = self::STEP_DEFAULT;
        }

        $this->step = $step;
    }
}
