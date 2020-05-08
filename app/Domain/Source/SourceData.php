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
        $page = 0;
        $n = 0;
        
        while ($n++ < 1000000) {
            $result = $this->invoker->invoke(['page' => $page, 'page_size' => $this->step]);
            
            if (!$result || !isset($result['status']) || $result['status'] !== 200 || !isset($result['data']['data'])) {
                throw new SourceException("获取源数据失败，返回错误：" . print_r($result, true), ErrCode::FETCH_SOURCE_FAILED);
            }

            $data = $result['data']['data'];

            // 第一次获取数据时将 key 写入
            if ($n == 1 && count($data)) {
                $this->sourceFile->saveData(array_keys($data[0]));
            }

            // 存储数据
            $this->sourceFile->saveData($data);

            if (count($data) < $this->step) {
                break;
            }

            $page++;
        }
    }
}
