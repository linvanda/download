<?php

namespace App\Domain\Object;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

/**
 * 目标文件
 * 目标文件由 meta（元数据） + data（数据） 构成，meta 决定目标文件的内容如何展现，data 则是要展现什么
 * 不同的目标文件的 meta 信息大不相同，因而此处使用数组存储，具体的目标文件自身定义数组格式，外界根据其约定格式传递参数
 */
abstract class Obj
{
    public const TYPE_CSV = 'csv';
    public const TYPE_EXCEL = 'excel';

    protected const FILE_EXT = [
        self::TYPE_CSV => ['csv'],
        self::TYPE_EXCEL => ['xlsx', 'xls'],
    ];

    // 目标文件名
    protected $fileName;
    // 目标文件类型
    protected $type;
    /**
     * @var array 元数据
     */
    protected $metaData = [];

    public function __construct(string $fileName = '', string $type = 'csv')
    {
        $this->setType($type);
        $this->setFileName($fileName);
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * 设置目标文件的元数据
     */
    public function setMeta(array $metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * 获取 meta 信息
     * @return mixed
     */
    public function getMeta(string $key = '')
    {
        return $key ? ($this->metaData[$key] ?? null) : $this->metaData;
    }

    abstract public function generate();

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
