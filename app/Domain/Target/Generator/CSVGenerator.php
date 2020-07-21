<?php

namespace App\Domain\Target\Generator;

use App\Foundation\File\ICompress;
use App\Domain\Target\CSV;
use App\Domain\Target\Template\Excel\RowHead;
use App\Domain\Source\Source;
use App\ErrCode;
use App\Exceptions\FileException;
use EasySwoole\EasySwoole\Config;
use Exception;
use WecarSwoole\Util\File;

/**
 * CSV 文件生成器
 */
class CSVGenerator
{
    /**
     * CSV 目标文件生成方式
     */
    public function generate(Source $source, CSV $target, ICompress $compress)
    {
        $sourceFileName = $source->fileName();
        if (!$sourceFileName || !file_exists($sourceFileName)) {
            throw new FileException("CSV 目标文件生成失败：源文件不存在。source：{$sourceFileName}", ErrCode::FILE_OP_FAILED);
        }

        // 直接通过 rename 生成目标文件
        if (rename($sourceFileName, $target->targetFileName()) === false) {
            throw new FileException("generate target file fail.rename failed.", ErrCode::FILE_OP_FAILED);
        }

        // 压缩
        if ($source->size() > Config::getInstance()->getConf("zip_threshold")) {
            $newTargetFileName = $compress->compress(File::join($target->getBaseDir(), 'target'), [$target->targetFileName()]);
            // 重新设置目标文件名字
            $target->setTargetFileName($newTargetFileName);
        }
    }
}
