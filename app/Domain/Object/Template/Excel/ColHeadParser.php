<?php

namespace App\Domain\Object\Template\Excel;

use App\ErrCode;
use EasySwoole\Component\Singleton;
use WecarSwoole\Exceptions\Exception;

/**
 * 列标头解析器
 */
class ColHeadParser
{
    use Singleton;

    private function __constructor()
    {
    }

    public function parse(array $config): ColHead
    {
        // 创建顶层节点
        $top = new ColHead('', '', null, ColHead::DT_STR);
        foreach ($config as $colCfg) {
            $top->appendChild($this->parseNode($colCfg));
        }

        return $top;
    }

    private function parseNode(array $colCfg): ColHead
    {
        $this->validate($colCfg);

        $styleCfg = $colCfg['style'] ?? [
            'bg_color' => $colCfg['bg_color'] ?? '',
            'width' => $colCfg['width'] ?? 0,
            'height' => $colCfg['height'] ?? 0,
            'align' => $colCfg['align'] ?? Style::ALIGN_CENTER,
            'color' => $colCfg['color'] ?? '',
            'bold' => $colCfg['bold'] ?? false,
        ];
        $style = new Style($styleCfg);

        $col = new ColHead($colCfg['name'] ?? '', $colCfg['title'] ?? '', $style, $colCfg['type'] ?? ColHead::DT_STR);

        if (isset($colCfg['children']) && $colCfg['children']) {
            foreach ($colCfg['children'] as $subColCfg) {
                $col->appendChild($this->parseNode($subColCfg));
            }
        }

        return $col;
    }

    private function validate(array $colCfg)
    {
        if (!$colCfg['name'] && !$colCfg['title']) {
            throw new Exception("模板格式错误：name 和 title 至少提供一个", ErrCode::PARAM_VALIDATE_FAIL);
        }
    }
}
