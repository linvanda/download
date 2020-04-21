<?php

namespace App\Domain\Project;

use EasySwoole\Utility\Random;
use WecarSwoole\Entity;

/**
 * 项目
 */
class Project extends Entity
{
    protected $id;
    protected $group;
    protected $name;
    protected $createTime;

    public function __construct(string $name, Group $group)
    {
        $this->id = Random::makeUUIDV4();
        $this->name = $name;
        $this->group = $group;
        $this->createTime = time();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function group(): Group
    {
        return $this->group;
    }
}
