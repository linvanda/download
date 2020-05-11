<?php

namespace App\Domain\Source;

use App\Domain\File\SourceFile;
use App\Domain\URI;
use App\ErrCode;
use App\Exceptions\SourceException;
use App\Foundation\Client\API;

/**
 * 数据源
 */
class Source
{
    public const STEP_MIN = 100;
    public const STEP_MAX = 1000;
    public const STEP_DEFAULT = 500;

    protected $uri;
    protected $step;

    public function __construct(URI $uri, int $step = 500)
    {
        $this->setStep($step);
        $this->uri = $uri;
    }

    public function uri(): URI
    {
        return $this->uri;
    }

    public function step(): int
    {
        return $this->step;
    }

    /**
     * 从源拉取数据保存到本地
     * @param API $invoker 源数据调用程序
     * @param SourceFile $sourceFile 源文件生成器
     */
    public function fetch(API $invoker, SourceFile $sourceFile)
    {
        $page = $n = $cnt = $total = 0;
        $invoker->setUrl($this->uri()->url());
        
        while ($n++ < 1000000) {
            $result = $invoker->invoke(['page' => $page, 'page_size' => $this->step]);
            
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

            $data = $result['data']['data'];
            $cnt += count($data);

            // 第一次获取数据时将 key 写入
            if ($n == 1 && count($data)) {
                $total = $result['data']['total'];
                $sourceFile->saveData(array_keys($data[0]));
            }

            // 存储数据
            $sourceFile->saveData($data);

            // 为了健壮性，此处做了两方面的检测，防止对方接口有 bug 导致一直拉取数据
            if (count($data) < $this->step || $cnt >= $total) {
                break;
            }

            $page++;
        }
    }

    protected function setStep(int $step)
    {
        if ($step < self::STEP_MIN || $step > self::STEP_MAX) {
            $step = self::STEP_DEFAULT;
        }

        $this->step = $step;
    }
}
