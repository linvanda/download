<?php

namespace App\Processor\WorkFlow\Handler;

use App\Processor\Ticket;
use App\Processor\WorkFlow\WorkFlow;

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
            // TODO 拉取源数据
            

            Ticket::done("task_source");
        });
    }
}
