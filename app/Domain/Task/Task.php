<?php

namespace App\Domain\Task;

use App\Domain\Object\ObjectFile;
use App\Domain\Project\Project;
use App\Domain\Source\Source;
use App\Domain\URI;
use App\ErrCode;
use WecarSwoole\Entity;
use WecarSwoole\Exceptions\Exception;

class Task extends Entity
{
    // 待处理
    public const STATUS_TODO = 1;
    // 处理中
    public const STATUS_DOING = 2;
    // 处理成功
    public const STATUS_SUC = 3;
    // 处理失败（该失败可重试，重试次数超限则会转入 STATUS_ERR）
    public const STATUS_FAILED = 4;
    // 处理失败，该失败不可重试
    public const STATUS_ERR = 5;

    /**
     * 状态转换表（状态机的查找表实现）
     * 二维数组的第一维的 key 表示当前状态，第二维的 key 表示新状态，第二维的 value 表示是否允许该状态转换
     * (注意状态的值是从 1 开始，而数组下标是从 0 开始，即将状态值 - 1)
     * （自己到自己如状态 a -> a 被认为是允许的，实际是没有任何转换）
     */
    private const STATUS_TRANS_MAP = [
        [true, true, false, false, false],
        [false, true, true, true, true],
        [false, false, true, false, false],
        [true, false, false, true, true],
        [false, false, false, false, true]
    ];

    // 任务 id
    protected $id;
    // 任务名称
    protected $name;
    // 所属的项目
    protected $project;
    // 数据源
    protected $source;
    // 目标文件
    protected $objectFile;
    // 回调通知 uri
    protected $callback;
    // 操作者编号
    protected $operator;
    // 任务创建时间
    protected $createTime;
    // 任务最后处理时间
    protected $lastExecTime;
    // 任务完成时间
    protected $finishedTime;
    // 任务状态
    protected $status;
    // 重试次数
    protected $retryNum;

    /**
     * 外界必须通过工厂方法来创建
     */
    public function __construct(
        string $id,
        string $name,
        Project $project,
        Source $source,
        ObjectFile $objectFile,
        URI $callback = null,
        string $operator = ''
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->project = $project;
        $this->source = $source;
        $this->objectFile = $objectFile;
        $this->callback = $callback;
        $this->operator = $operator;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function project(): Project
    {
        return $this->project;
    }

    public function source(): Source
    {
        return $this->source;
    }

    public function objectFile(): ObjectFile
    {
        return $this->objectFile;
    }

    public function callbackURI(): URI
    {
        return $this->callback;
    }

    public function status(): int
    {
        return $this->status;
    }

    /**
     * 更改任务状态
     */
    public function switchStatus(int $newStatus)
    {
        // 查找表数组下表从 0 开始，要用状态值 - 1
        $newPos = $newStatus - 1;
        $oldPos = $this->status - 1;
        $canTrans = self::STATUS_TRANS_MAP[$oldPos][$newPos];

        if (!$canTrans) {
            throw new Exception("非法的状态切换：{$this->status} -> {$newStatus}", ErrCode::INVALID_STATUS_OP);
        }

        $this->status = $newStatus;
    }
}
