<?php

namespace App\Processor\Monitor;

use App\Foundation\Queue\Queue;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use Psr\Log\LoggerInterface;
use WecarSwoole\Container;

/**
 * 队列监控程序
 * 用链表记录每次检查的信息
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
    private $sizeInfo;

    public function __construct()
    {
        $this->sizeInfo = [
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

            $this->addNode(new SizeNode($size, time()));

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

        $this->removeExpiredNode();

        // 计算均值，记录次数小于 2 的不考虑
        $bk = $this->sizeInfo;
        $fiveAvg = $bk[self::T_FIVE][1] < 3 ? 0 : $bk[self::T_FIVE][0] / $bk[self::T_FIVE][1];
        $fifteenAvg = $bk[self::T_FIFTEEN][1] < 3 ? 0 : $bk[self::T_FIFTEEN][0] / $bk[self::T_FIFTEEN][1];
        $thirtyAvg = $bk[self::T_THIRTY][1] < 3 ? 0 : $bk[self::T_THIRTY][0] / $bk[self::T_THIRTY][1];

        // 告警
        if ($fiveAvg >= self::THRESHOLD_FIVE || $fifteenAvg >= self::THRESHOLD_FIFTEEN || $thirtyAvg >= self::THRESHOLD_THIRTY) {
            $msg = "下载中心任务队列负载（队列长度）告警。5 分钟：{$fiveAvg}，15 分钟：{$fifteenAvg}，30 分钟：{$thirtyAvg}，峰值：{$this->peakSize}，最近：{$this->latestSize}";
            Container::get(LoggerInterface::class)->critical($msg);
        }
    }

    private function removeExpiredNode()
    {
        // 删除多余的节点（最大游标后面的节点）
        while (1) {
            if (!$this->head || !$this->head->next() || $this->head === $this->thirtyPoint) {
                break;
            }

            // 将 head 后移
            $this->head = $this->head->next();
        }
    }

    /**
     * 更新分时游标的位置，保证游标指向的元素以及其前面的元素在分时有效期内
     */
    private function updatePoint(int $flag, SizeNode &$pointer)
    {
        if (!$pointer) {
            $pointer = $this->head;
            return;
        }

        $currNode = $pointer;
        $now = time();
        while (1) {
            if ($currNode->time() > $now - 60 * $flag || !$currNode->next()) {
                break;
            }

            // 需要向尾部迁移，迁移前从 bucket 中减掉相应的 size 和次数
            $this->updateBucket($flag, $currNode->size() * -1, -1);
            $currNode = $currNode->next();
        }

        $pointer = $currNode;
    }

    /**
     * 将元素添加到链表中
     */
    private function addNode(SizeNode $node)
    {
        // 将新节点加到表尾巴
        if (!$this->head) {
            $this->head = $node;
            $this->tail = $node;
            $this->fivePoint = $node;
            $this->fifteenPoint = $node;
            $this->thirtyPoint = $node;
            return;
        }

        // 移动尾指针
        $this->tail->setNext($node);
        $this->tail = $node;
    }

    /**
     * 更新统计信息
     */
    private function updateBucket(int $flag, int $incrSize, int $incrCount = 1)
    {
        if (!isset($this->sizeInfo[$flag])) {
            return;
        }

        $this->sizeInfo[$flag][0] += $incrSize;
        $this->sizeInfo[$flag][1] += $incrCount;
    }
}
