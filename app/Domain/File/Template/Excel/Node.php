<?php

namespace App\Domain\File\Template\Excel;

class Node
{
    protected $name;
    protected $title;
    protected $style;
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
}
