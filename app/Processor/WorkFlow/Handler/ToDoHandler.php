<?php

namespace App\Processor\WorkFlow\Handler;

use App\Domain\Object\ObjService;
use App\Domain\Source\SourceService;
use App\Processor\WorkFlow\WorkFlow;
use WecarSwoole\Container;

/**
 * 待处理处理程序
 */
class ToDoHandler extends WorkHandler
{
    public function handleStatus(): int
    {
        return WorkFlow::WF_TODO;
    }

    /**
     * 启动新协程拉取源数据
     */
    protected function exec()
    {
        try {
            // 获取动态元数据
            Container::get(ObjService::class)->fetchDynamicMeta($this->task());
            // 获取数据
            Container::get(SourceService::class)->fetch($this->task());
            $this->notify(WorkFlow::WF_SOURCE_READY);
        } catch (\Exception $e) {
            $this->notify(WorkFlow::WF_SOURCE_FAILED, $e->getMessage());
        }
    }
}
