<?php

namespace App\Processor\WorkFlow\Handler;

use App\Domain\Source\SourceDataService;
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
                Container::get(SourceDataService::class)->fetch($this->task());
                // 获取源数据成功
                $this->notify(WorkFlow::WF_SOURCE_READY);
            } catch (\Exception $e) {
                // 获取源数据失败，更新 task 状态

                $this->notify(WorkFlow::WF_SOURCE_FAILED);
            } finally {
                Ticket::done("task_source");
            }
        });
    }
}
