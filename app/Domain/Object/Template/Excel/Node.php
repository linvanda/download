<?php

namespace App\Domain\Object\Template\Excel;

/**
 * 标头节点
 */
class Node
{
    protected $name;
    protected $title;
    /**
     * @var Style
     */
    protected $style;
    /**
     * @var array
     */
    protected $children;

    public function __construct(string $name = '', string $title = '', Style $style = null)
    {
        $this->name = $name;
        $this->title = $title;
        $this->style = $style;
        $this->children = [];
    }

    public function appendChild(Node $node)
    {
        $this->children[] = $node;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function style(): Style
    {
        return $this->style;
    }

    public function children(): array
    {
        return $this->children;
    }

    /**
     * 树深度
     */
    public function deep(): int
    {
        return $this->detectDeep($this);
    }

    /**
     * 树广度
     */
    public function breadth(): int
    {
        return $this->detectBreadth($this);
    }

    /**
     * 根据节点名称查找节点
     */
    public function search(string $name): ?Node
    {
        return $this->searchNode($name, $this);
    }

    private function searchNode(string $name, Node $node): ?Node
    {
        if ($node->name() === $name) {
            return $node;
        }

        if (!$node->children()) {
            return null;
        }

        foreach ($node->children() as $childNode) {
            // 只要找到则立即返回，不再继续查找
            if ($theNode = $this->searchNode($name, $childNode)) {
                return $theNode;
            }
        }

        return null;
    }

    /**
     * 深度探测：取各条线路探测结果的最大值
     */
    private function detectDeep(Node $node, int $deep = 1): int
    {
        if (!$node->children()) {
            return $deep;
        }

        $maxDeep = $deep;
        foreach ($node->children() as $childNode) {
            $maxDeep = max($maxDeep, $this->detectDeep($childNode, ++$deep));
        }

        return $maxDeep;
    }

    /**
     * 广度探测：遇到一个没有 children 的节点则广度加 1
     */
    private function detectBreadth(Node $node, int &$breadth = 0): int
    {
        if (!$node->children()) {
            return ++$breadth;
        }

        foreach ($node->children() as $childNode) {
            $this->detectBreadth($childNode, $breadth);
        }

        return $breadth;
    }
}
