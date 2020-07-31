<?php

namespace App\Processor\Monitor;

use App\Foundation\Queue\Queue;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use Psr\Log\LoggerInterface;
use WecarSwoole\Container;

/**
 * 队列监控程序
 * 使用双向链表记录每次检查的信息
 * （也可以用单向链表：向表尾插入新元素，从表头删除旧元素）
 */
class QueueMonitor
{
    use Singleton;

    private const THRESHOLD_FIVE = 30;
    private const THRESHOLD_FIFTEEN = 20;
    private const THRESHOLD_THIRTY = 10;
    private const T_FIVE = 5;
    private const T_FIFTEEN = 15;
    private const T_THIRTY = 30;

    // 峰值
    private $peakSize;
    // 最近一次大小
    private $latestSize;
    // 最后检查时间
    private $lastCheckTime;
    /**
     * 链表头
     * @var SizeNode
     */
    private $head;
    /**
     * 链表尾
     * @var SizeNode
     */
    private $tail;
    // 5 分钟指针
    private $fivePoint;
    // 15 分钟指针
    private $fifteenPoint;
    // 30 分钟指针
    private $thirtyPoint;
    private $sizeBuckets;

    public function __construct()
    {
        $this->sizeBuckets = [
            self::T_FIVE => [0, 0],// 格:[total_size, count]，均值算法：total_size/count
            self::T_FIFTEEN => [0, 0],
            self::T_THIRTY => [0, 0],
        ];
    }

    public function watch()
    {
        try {
            $size = Queue::instance(Config::getInstance()->getConf('task_queue'))->size();
            if ($size > $this->peakSize) {
                $this->peakSize = $size;
            }
            $this->latestSize = $size;

            $this->addToHead(new SizeNode($size, time()));

            // 更新 bucket 数据
            $this->updateBucket(self::T_FIVE, $size, 1);
            $this->updateBucket(self::T_FIFTEEN, $size, 1);
            $this->updateBucket(self::T_THIRTY, $size, 1);

            // 计算
            $this->calc();
        } catch (\Exception $e) {
            Container::get(LoggerInterface::class)->critical("redis 检测错误：{$e->getMessage()}");
        }
    }

    private function calc()
    {
        // 更新每个游标的位置
        $this->updatePoint(self::T_FIVE, $this->fivePoint);
        $this->updatePoint(self::T_FIFTEEN, $this->fifteenPoint);
        $this->updatePoint(self::T_THIRTY, $this->thirtyPoint);

        // 删除多余的节点（最大游标后面的节点）
        while (1) {
            if (!$this->tail || !$this->tail->prev() || $this->tail === $this->thirtyPoint) {
                break;
            }

            // 将 tail 前移
            $this->tail->prev()->setNext(null);
            $this->tail = $this->tail->prev();
        }

        // 计算均值，记录次数小于 2 的不考虑
        $bk = $this->sizeBuckets;
        $fiveAvg = $bk[self::T_FIVE][1] < 3 ? 0 : $bk[self::T_FIVE][0] / $bk[self::T_FIVE][1];
        $fifteenAvg = $bk[self::T_FIFTEEN][1] < 3 ? 0 : $bk[self::T_FIFTEEN][0] / $bk[self::T_FIFTEEN][1];
        $thirtyAvg = $bk[self::T_THIRTY][1] < 3 ? 0 : $bk[self::T_THIRTY][0] / $bk[self::T_THIRTY][1];

        // 告警
        if ($fiveAvg >= self::THRESHOLD_FIVE || $fifteenAvg >= self::THRESHOLD_FIFTEEN || $thirtyAvg >= self::THRESHOLD_THIRTY) {
            $msg = "下载中心任务队列负载（队列长度）告警。5 分钟：{$fiveAvg}，15 分钟：{$fifteenAvg}，30 分钟：{$thirtyAvg}，峰值：{$this->peakSize}，最近：{$this->latestSize}";
            Container::get(LoggerInterface::class)->critical($msg);
        }
    }

    /**
     * 更新分时游标的位置，保证游标指向的元素以及其前面的元素在分时有效期内
     */
    private function updatePoint(int $flag, SizeNode &$pointer)
    {
        if (!$pointer) {
            $pointer = $this->tail;
            return;
        }

        $currNode = $pointer;
        $now = time();
        while (1) {
            if ($currNode->time() > $now - 60 * $flag || !$currNode->prev()) {
                break;
            }

            // 需要向头部迁移，迁移前从 bucket 中减掉相应的 size 和次数
            $this->updateBucket($flag, $currNode->size() * -1, -1);
            $currNode = $currNode->prev();
        }

        $pointer = $currNode;
    }

    /**
     * 在双向链表头部增加元素
     */
    private function addToHead(SizeNode $node)
    {
        // 将新节点加到表头位置
        if (!$this->head) {
            $this->head = $node;
        } else {
            $node->setNext($this->head);
            $this->head->setPrev($node);
            $this->head = $node;
        }

        // 设置尾指针
        if (!$this->tail) {
            $this->tail = $node;
            $this->fivePoint = $node;
            $this->fifteenPoint = $node;
            $this->thirtyPoint = $node;
        }
    }

    /**
     * 更新统计信息
     */
    private function updateBucket(int $flag, int $incrSize, int $incrCount = 1)
    {
        if (!isset($this->sizeBuckets[$flag])) {
            return;
        }

        $this->sizeBuckets[$flag][0] += $incrSize;
        $this->sizeBuckets[$flag][1] += $incrCount;
    }
}
