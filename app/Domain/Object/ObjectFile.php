<?php

namespace App\Domain\Object;

use App\Domain\Object\Template\Excel\Tpl;
use App\Domain\Object\Template\Excel\TplFactory;
use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

/**
 * 目标文件
 */
class ObjectFile
{
    public const TYPE_CSV = 'csv';
    public const TYPE_EXCEL = 'excel';

    protected const FILE_EXT = [
        self::TYPE_CSV => ['csv'],
        self::TYPE_EXCEL => ['xlsx', 'xls'],
    ];

    protected $fileName;
    protected $type;
    /**
     * @var Tpl
     */
    protected $tpl;

    public function __construct(string $fileName = '', string $type = 'csv', $tpl = null)
    {
        $this->setType($type);
        $this->setFileName($fileName);
        $this->setTemplate($tpl);
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function template(): ?Tpl
    {
        return $this->tpl;
    }

    public function setTemplate($tpl)
    {
        $this->tpl = $tpl === null || $tpl instanceof Tpl ? $tpl : TplFactory::build($tpl);
    }

    protected function setType(string $type)
    {
        if (!in_array($type, [self::TYPE_CSV, self::TYPE_EXCEL])) {
            throw new Exception("目标文件类型不合法", ErrCode::PARAM_VALIDATE_FAIL);
        }

        $this->type = $type;
    }

    protected function setFileName(string $name)
    {
        if (!$name) {
            $name = self::generateFileName();
        }

        $this->fileName = $this->appendFileExt($name);
    }

    protected static function generateFileName(): string
    {
        return date('YmdHis');
    }

    protected function appendFileExt(string $fileName): string
    {
        if ($dotPos = strrpos($fileName, '.')) {
            $ext = substr($fileName, $dotPos + 1);
        }
     
        if (isset($ext) && in_array($ext, self::FILE_EXT[$this->type])) {
            return $fileName;
        }

        return $fileName . "." . self::FILE_EXT[$this->type][0];
    }
}
