<?php

namespace App\Domain\Source;

use App\ErrCode;
use App\Exceptions\FileException;
use App\Exceptions\SourceException;
use App\Foundation\Client\API;

/**
 * 源数据
 */
class SourceData
{
    private $step;
    private $invoker;
    private $saveToFile;

    /**
     * @param API $invoker 数据源获取接口
     * @param string $saveToFile 获取到的数据保存到哪里去
     * @param int $step 获取数据的步长
     */
    public function __construct(API $invoker, string $saveToFile, int $step = 500)
    {
        $this->step = $step;
        $this->invoker = $invoker;
        $this->saveToFile = $saveToFile;
    }

    /**
     * 从源拉取数据保存到本地
     */
    public function fetch()
    {
        $file = $this->openFile();

        $page = 0;
        $n = 0;
        
        try {
            while ($n++ < 1000000) {
                $result = $this->invoker->invoke(['page' => $page, 'page_size' => $this->step]);
                
                if (!$result || !isset($result['status']) || $result['status'] !== 200 || !isset($result['data']['data'])) {
                    throw new SourceException("获取源数据失败，返回错误：" . print_r($result, true), ErrCode::FETCH_SOURCE_FAILED);
                }
    
                $data = $result['data']['data'];

                // 第一次获取数据时将 key 写入
                if ($n == 1 && count($data)) {
                    $this->saveData(array_keys($data[0]), $file);
                }

                $this->saveData($data, $file);
    
                if (count($data) < $this->step) {
                    break;
                }
    
                $page++;
            }
        } catch (\Exception $e) {
            throw new SourceException($e->getMessage(), $e->getCode());
        } finally {
            fclose($file);
        }
    }

    /**
     * 存储为临时文件
     */
    private function saveData(array $dataList, $file)
    {
        foreach ($dataList as $item) {
            if (fputcsv($file, array_values($item)) === false) {
                throw new FileException("写入文件失败:{$this->saveToFile}", ErrCode::FETCH_SOURCE_FAILED);
            }
        }
    }

    private function openFile()
    {
        $dir = basename($this->saveToFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0644, true);
        }

        $file = fopen($this->saveToFile, 'w');
        if ($file === false) {
            throw new FileException("打开文件失败:{$this->saveToFile}", ErrCode::FETCH_SOURCE_FAILED);
        }

        return $file;
    }
}
