<?php

namespace App\Processor\WorkFlow\Handler;

use App\Domain\Object\ObjService;
use App\Processor\WorkFlow\WorkFlow;
use WecarSwoole\Container;

/**
 * 源数据就绪处理程序
 */
class SourceReadyHandler extends WorkHandler
{
    public function handleStatus(): int
    {
        return WorkFlow::WF_SOURCE_READY;
    }

    /**
     * 生成目标数据
     */
    protected function exec()
    {
        try {
            Container::get(ObjService::class)->generate($this->task());
            $this->notify(WorkFlow::WF_OBJECT_READY);
        } catch (\Exception $e) {
            $this->notify(WorkFlow::WF_OBJECT_FAILED);
        }
    }
}
