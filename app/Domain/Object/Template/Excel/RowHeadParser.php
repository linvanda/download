<?php

namespace App\Domain\Object\Template\Excel;

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

    public function parse(array $config): RowHead
    {
        // 创建顶层节点
        $top = new RowHead(Node::NODE_TOP, '', null);
        foreach ($config as $index => $rowCfg) {
            $top->appendChild($this->parseNode($rowCfg, 1, $index));
        }

        return $top;
    }

    private function parseNode(array $rowCfg, int $rowNum, int $colNum): RowHead
    {
        $styleCfg = $rowCfg['style'] ?? ['bg_color' => $rowCfg['bg_color'] ?? ''];
        $style = new Style($styleCfg);
        $row = new RowHead($rowCfg['name'] ?? '', $rowCfg['title'] ?? '', $style);
        $row->setPosition($rowNum, $colNum);

        if (isset($rowCfg['children']) && $rowCfg['children']) {
            foreach ($rowCfg['children'] as $index => $subColCfg) {
                $row->appendChild($this->parseNode($subColCfg, $rowNum + 1, $index));
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
