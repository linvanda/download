<?php

namespace App\Domain\Project;

use EasySwoole\Utility\Random;
use WecarSwoole\Entity;

/**
 * 项目组
 */
class Group extends Entity
{
    protected $id;
    protected $name;

    public function __construct($name)
    {
        $this->id = Random::makeUUIDV4();
        $this->name = $name;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}
