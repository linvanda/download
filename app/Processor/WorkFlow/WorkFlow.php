<?php

namespace App\Processor\WorkFlow;

use App\Domain\Task\Task;
use App\Processor\TaskManager;
use App\Processor\WorkFlow\Handler\ObjectReadyHandler;
use App\Processor\WorkFlow\Handler\ReDoHandler;
use App\Processor\WorkFlow\Handler\SourceReadyHandler;
use App\Processor\WorkFlow\Handler\ToDoHandler;
use App\Processor\WorkFlow\Handler\UploadSuccessHandler;
use App\Processor\WorkFlow\Handler\WorkHandler;
use EasySwoole\Component\Singleton;

/**
 * 工作流
 * 使用链表实现调用链
 */
class WorkFlow
{
    use Singleton;

    /**
     * 工作流状态定义
     * 除了初始态和结束态，其他状态必须要有对应的节点处理程序
     */
    // 工作流初始化（该状态没有对应的节点处理程序，仅用来标志工作流初始态）
    public const WF_INIT = 1;
    // 待处理
    public const WF_TODO = 2;
    // 源数据就绪（源数据已经全部拉到本地形成临时文件。注意：对方接口返回空数据也认为是就绪）
    public const WF_SOURCE_READY = 3;
    // 源数据获取失败 （未成功获取全部源数据，可能对方接口不可用）
    public const WF_SOURCE_FAILED = 4;
    // 目标文件就绪（已生成目标文件到本地）
    public const WF_OBJECT_READY = 5;
    // 目标文件生成失败
    public const WF_OBJECT_FAILED = 6;
    // 上传完成
    public const WF_UPLOAD_SUC = 7;
    // 上传失败
    public const WF_UPLOAD_FAILED = 8;
    // 重试
    public const WF_REDO = 9;
    // 通知客户端完成
    public const WF_NOTIFY_DONE = 10;
    // 通用的执行完成状态，该状态没有处理程序处理，一般用来结束工作流
    public const WF_DONE = 100;

    // 工作流第一个执行节点
    private $firstStatus = self::WF_TODO;

    /**
     * @var WorkHandler 工作流头节点处理程序，用来启动职责链的调用
     */
    private $head;
    /**
     * @var WorkHandler 尾节点处理程序，用来添加新的处理程序
     */
    private $tail;
    /**
     * @var Task 工作流对应的任务
     */
    private $task;
    /**
     * @var int 工作流当前执行状态
     */
    private $currentStatus;
    // 该工作流能够处理的状态（节点）列表
    private $handleStatus = [];

    private function __construct(Task $task)
    {
        $this->task = $task;
        $this->currentStatus = self::WF_INIT;
    }

    /**
     * 工作流对应的任务
     */
    public function task(): Task
    {
        return $this->task;
    }

    /**
     * 工作流当前状态
     */
    public function status(): int
    {
        return $this->currentStatus;
    }

    /**
     * 启动工作流
     */
    public function start()
    {
        $this->notify($this->firstStatus);
    }

    /**
     * 通知工作流执行
     * 由于有些步骤可能是在单独的子协程或者 task 进程中执行的，只能通过调用此方法异步通知来告知工作流引擎当前是什么进度
     */
    public function notify(int $workStatus)
    {
        $this->currentStatus = $workStatus;
        if (!in_array($workStatus, $this->handleStatus)) {
            // 没有处理程序处理该状态，结束工作流
            TaskManager::getInstance()->finish($this->task);
            return;
        }

        $this->handle($workStatus);
    }

    /**
     * 创建一个新的工作流
     */
    public static function newWorkFlow(Task $task): WorkFlow
    {
        $workFlow = new self($task);

        // 添加节点处理程序
        $workFlow->addHandler(new ToDoHandler($workFlow))
             ->addHandler(new SourceReadyHandler($workFlow))
             ->addHandler(new ObjectReadyHandler($workFlow))
             ->addHandler(new UploadSuccessHandler($workFlow))
             ->addHandler(new ReDoHandler($workFlow));

        return $workFlow;
    }

    /**
     * 执行工作流节点
     */
    protected function handle(int $workStatus)
    {
        $this->head->handle($workStatus);
    }

    /**
     * 添加节点处理程序
     */
    private function addHandler(WorkHandler $workHandler): WorkFlow
    {
        if (in_array($workHandler->handleStatus(), $this->handleStatus)) {
            return $this;
        }

        if (!$this->head) {
            $this->head = $workHandler;
        } else {
            $this->tail->setSuccessor($workHandler);
        }
        $this->tail = $workHandler;

        $this->handleStatus[] = $workHandler->handleStatus();

        return $this;
    }
}
