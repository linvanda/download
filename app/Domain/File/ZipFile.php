<?php

namespace App\Domain\File;

use ZipArchive;

/**
 * zip 格式归档压缩
 */
class ZipFile implements ICompress
{
    public function compress(string $toFileName, array $fromFiles)
    {
        if (!$fromFiles) {
            return;
        }

        $zip = new ZipArchive();
        $zip->open($toFileName, ZipArchive::CREATE);
        foreach ($fromFiles as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    }
}
