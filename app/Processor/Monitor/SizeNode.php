<?php

namespace App\Processor\Monitor;

/**
 * 双向链表节点
 */
class SizeNode
{
    private $size;
    private $time;
    private $next;
    private $prev;

    public function __construct(int $size, int $time)
    {
        $this->size = $size;
        $this->time = $time;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function time(): int
    {
        return $this->time;
    }

    public function next(): ?SizeNode
    {
        return $this->next;
    }

    public function prev(): ?SizeNode
    {
        return $this->prev;
    }

    public function setNext(SizeNode $node = null)
    {
        $this->next = $node;
    }

    public function setPrev(SizeNode $node = null)
    {
        $this->prev = $node;
    }
}
