<?php

namespace App\Domain\Task;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

/**
 * 数据源
 */
class Source
{
    public const STEP_MIN = 100;
    public const STEP_MAX = 1000;

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
            throw new Exception("step范围不合法", ErrCode::PARAM_VALIDATE_FAIL);
        }

        $this->step = $step;
    }
}
