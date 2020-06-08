<?php

namespace App\Domain\Object\Template\Excel;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

/**
 * 列标头
 */
class ColHead extends Node
{
    public const DT_STR = 'string';// 默认类型
    public const DT_NUM = 'number';
    public const DT_RICH = 'rich';// 富文本（支持 html 标签）
    public const ROW_HEAD_COL_NAME = '_row_header';

    /**
     * 列数据类型
     */
    protected $dataType;

    public function __construct(string $name = '', string $title = '', Style $style = null, string $dataType = self::DT_STR)
    {
        $this->resolveDataType($dataType);
        parent::__construct($name, $title, $style);
    }

    private function resolveDataType(string $dataType)
    {
        if (!$dataType) {
            $this->dataType = self::DT_STR;
            return;
        }

        $dataType = strtolower($dataType);

        if (!in_array($dataType, [self::DT_STR, self::DT_NUM, self::DT_RICH])) {
            throw new Exception("模板错误：数据格式不合法：{$dataType}", ErrCode::PARAM_VALIDATE_FAIL);
        }

        $this->dataType = $dataType;
    }
}
