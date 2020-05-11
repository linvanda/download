<?php

namespace App\Processor\WorkFlow\Handler;

use App\Domain\Source\SourceService;
use App\Processor\Ticket;
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
        // 获取票据（用于限制并发量）
        Ticket::get("task_source");

        go(function () {
            try {
                Container::get(SourceService::class)->fetch($this->task());
                $this->notify(WorkFlow::WF_SOURCE_READY);
            } catch (\Exception $e) {
                $this->notify(WorkFlow::WF_SOURCE_FAILED, $e->getMessage());
            } finally {
                Ticket::done("task_source");
            }
        });
    }
}
