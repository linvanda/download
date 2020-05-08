<?php

namespace App\Domain\Source;

use App\Domain\URI;

/**
 * 数据源
 */
class Source
{
    public const STEP_MIN = 100;
    public const STEP_MAX = 1000;
    public const STEP_DEFAULT = 500;

    protected $uri;
    protected $step;

    public function __construct(URI $uri, int $step = 500)
    {
        $this->setStep($step);
        $this->uri = $uri;
    }

    public function uri(): URI
    {
        return $this->uri;
    }

    public function step(): int
    {
        return $this->step;
    }

    protected function setStep(int $step)
    {
        if ($step < self::STEP_MIN || $step > self::STEP_MAX) {
            $step = self::STEP_DEFAULT;
        }

        $this->step = $step;
    }
}
