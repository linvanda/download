<?php

namespace App\Domain\File\Template\Excel;

use App\ErrCode;
use EasySwoole\Component\Singleton;
use WecarSwoole\Exceptions\Exception;

/**
 * 行标头解析器
 */
class RowHeadParser
{
    use Singleton;

    private function __constructor()
    {
    }

    public function parse(array $rowCfg): RowHead
    {
        $styleCfg = $rowCfg['style'] ?? ['bg_color' => $rowCfg['bg_color'] ?? ''];
        $style = new Style($styleCfg);
        $row = new RowHead($rowCfg['name'] ?? '', $rowCfg['title'] ?? '', $style);

        if (isset($rowCfg['children']) && $rowCfg['children']) {
            foreach ($rowCfg['children'] as $subColCfg) {
                $row->appendChild($this->parse($subColCfg));
            }
        }

        return $row;
    }

    private function validate(array $rowCfg)
    {
        if (!$rowCfg['name'] && !$rowCfg['title']) {
            throw new Exception("模板格式错误：name 和 title 至少提供一个", ErrCode::PARAM_VALIDATE_FAIL);
        }
    }
}
