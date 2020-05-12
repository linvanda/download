<?php

namespace App\Domain\File;

/**
 * 文件压缩接口
 */
interface ICompress
{
    /**
     * 压缩
     * @param string $toFileName 压缩文件名称
     * @param array $fromFiles 需要压缩的文件列表
     */
    public function compress(string $toFileName, array $fromFiles);
}
