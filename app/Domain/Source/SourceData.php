<?php

namespace App\Domain\Source;

use App\Domain\File\SourceFile;
use App\ErrCode;
use App\Exceptions\SourceException;
use App\Foundation\Client\API;

/**
 * 源数据
 */
class SourceData
{
    private $step;
    private $invoker;
    private $sourceFile;

    /**
     * @param API $invoker 数据源获取接口
     * @param SourceFile $sourceFile 获取到的数据保存到哪里去
     * @param int $step 获取数据的步长
     */
    public function __construct(API $invoker, SourceFile $sourceFile, int $step = 500)
    {
        $this->step = $step;
        $this->invoker = $invoker;
        $this->sourceFile = $sourceFile;
    }

    /**
     * 从源拉取数据保存到本地
     */
    public function fetch()
    {
        $page = $n = $cnt = $total = 0;
        
        while ($n++ < 1000000) {
            $result = $this->invoker->invoke(['page' => $page, 'page_size' => $this->step]);
            
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
                $this->sourceFile->saveData(array_keys($data[0]));
            }

            // 存储数据
            $this->sourceFile->saveData($data);

            // 为了健壮性，此处做了两方面的检测，防止对方接口有 bug 导致一直拉取数据
            if (count($data) < $this->step || $cnt >= $total) {
                break;
            }

            $page++;
        }
    }
}
