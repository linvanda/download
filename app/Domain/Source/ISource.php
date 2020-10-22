<?php

namespace App\Domain\Source;

use App\Foundation\Client\API;

/**
 * 数据源接口
 */
interface ISource
{
    /**
     * 源文件名称（包含目录）
     */
    public function fileName(): string;

    /**
     * 数据记录数（行数）
     */
    public function count(): int;

    /**
     * 源文件大小，单位字节
     */
    public function size(): int;

    /**
     * 从源拉取数据并保存到本地
     * @param API $invoker 源数据调用程序
     * @param bool $recordColType 是否记录列类型
     */
    public function fetch(API $invoker, bool $recordColType = true);

    /**
     * 从源拉取元数据
     */
    public function fetchMeta(API $invoker): array;
}
