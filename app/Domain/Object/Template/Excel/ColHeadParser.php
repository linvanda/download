<?php

namespace App\Domain\Object\Template\Excel;

use App\ErrCode;
use EasySwoole\Component\Singleton;
use SplQueue;
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

    /**
     * 根据配置文件解析出 excel 节点树
     */
    public function parse(array $config): ColHead
    {
        // 加入顶层节点
        if ($config['name'] !== Node::NODE_TOP) {
            $conf = [
                'name' => Node::NODE_TOP,
                'children' => $config,
            ];
        } else {
            $conf = $config;
        }

        $node = $this->parseNode($conf);

        // 计算每个节点在 Excel 中的位置
        $this->calcPosition($node);

        return $node;
    }

    private function calcPosition(Node $node)
    {
        $this->calcPos($node, -1, 0, []);
    }

    /**
     * 第一个子节点列号和父节点的相同
     * 后续子节点相对于父节点的列偏移量是前面所有子节点的广度之和
     * @param Node $node 要计算的节点
     * @param int $parentRowNum 父节点行号
     * @param int $parentColNum 父节点列号
     * @param array $neighbours 本节点的前置邻居节点列表（邻居是指同一个父节点下的节点）
     */
    private function calcPos(Node $node, int $parentRowNum, int $parentColNum, array $neighbours)
    {
        $row = $parentRowNum + 1;
        if (!$neighbours) {
            // 没有前置邻居（第一个节点），则取父节点的列号
            $col = $parentColNum;
        } else {
            // 否则，取父节点列号 + 所有邻居广度之和（相对于父节点的列偏移）
            $offset = 0;
            foreach ($neighbours as $neighbour) {
                $offset += $neighbour->breadth();
            }

            $col = $parentColNum + $offset;
        }

        $node->setPosition($row, $col);

        $nbs = [];
        foreach ($node->children() as $subNode) {
            $this->calcPos($subNode, $row, $col, $nbs);
            $nbs[] = $subNode;
        }
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
