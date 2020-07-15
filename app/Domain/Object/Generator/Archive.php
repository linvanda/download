<?php

namespace App\Domain\Object\Generator;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;
use ZipArchive;

/**
 * 文件归档、压缩
 */
class Archive
{
    protected $archiveFileName;
    protected $origFileNames;
    protected $delOrigFile;

    /**
     * @param string $archiveFileName 归档后文件
     * @param array $origFileNames 要归档的文件数组
     * @param bool $delOrigFile 归档后是否删除源文件
     */
    public function __construct(string $archiveFileName, array $origFileNames, bool $delOrigFile = true)
    {
        $this->archiveFileName = strpos($archiveFileName, '.') === false ? $archiveFileName . '.zip' : $archiveFileName;
        $this->origFileNames = $origFileNames;
        $this->delOrigFile = $delOrigFile;
    }
    
    public function __invoke(): string
    {
        $zip = new ZipArchive();

        if (!file_exists($this->archiveFileName)) {
            // 先创建文件，否则有些操作系统报错
            touch($this->archiveFileName);
            chmod($this->archiveFileName, 0755);
        }

        if ($zip->open($this->archiveFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("can not open zip file", ErrCode::FILE_OP_FAILED);
        }

        foreach ($this->origFileNames as $index => $fileName) {
            if (!is_readable($fileName)) {
                continue;
            }
            if (!$zip->addFile($fileName, strval($index + 1) . '.' . explode('.', $fileName)[1])) {
                throw new Exception("add file to zip failed", ErrCode::FILE_OP_FAILED);
            }
        }

        $zip->close();

        // 必须在 zip close 后才能删除文件
        if ($this->delOrigFile) {
            foreach ($this->origFileNames as $fileName) {
                unlink($fileName);
            }
        }

        return $this->archiveFileName;
    }
}
