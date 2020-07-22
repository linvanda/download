<?php

namespace App\Processor;

use App\Bootstrap;
use App\Domain\Task\ITaskRepository;
use App\Foundation\File\LocalFile;
use App\Processor\Monitor\QueueMonitor;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Config;
use WecarSwoole\Container;
use WecarSwoole\Util\File;
use Swoole\Timer;
use Closure;
use Psr\Log\LoggerInterface;

/**
 * 后台守卫程序，执行失败重试、数据归档的任务
 * Class Defender
 */
class Defender extends AbstractProcess
{
    private $queueSizeBucket = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function run($arg)
    {
        Bootstrap::boot();

        // 只有主服才执行守卫程序
        if (!in_array(Config::getInstance()->getConf('master_server'), swoole_get_local_ip())) {
            return;
        }

        $this->logger = Container::get(LoggerInterface::class);
        $this->logger->info('启动守卫程序');

        // 10 秒一次，执行失败重试
        Timer::tick(10000, Closure::fromCallable([$this, 'retryTask']));
        // 30 秒一次，检测队列状态
        Timer::tick(30000, Closure::fromCallable([QueueMonitor::getInstance(), 'check']));
        // 30 分钟一次，清理目录中的无用文件
        Timer::tick(1800000, Closure::fromCallable([$this, 'clearDir']));
        // 3 小时一次，归档 task 数据
        Timer::tick(10800000, Closure::fromCallable([$this, 'fileData']));
    }

    public function onShutDown()
    {
        // nothing
    }

    public function onReceive(string $str)
    {
        // nothing
    }

    /**
     * 任务失败重试
     * 以下状态需要重试：
     *  1. 待处理（状态码：1，未入列）：
     *      a. 入列失败；
     *      b. 入列成功但改状态失败；
     *      c. 从其它异常状态转成待处理状态的；
     *    重试方案：
     */
    private function retryTask()
    {
        
    }

    /**
     * 清理最后修改时间是 6 小时前的目录
     */
    private function clearDir()
    {
        $dir = Config::getInstance()->getConf('local_file_base_dir');

        try {
            foreach (scandir($dir) as $subDir) {
                if ($subDir == '.' || $subDir == '..') {
                    continue;
                }
                
                $realDir = File::join($dir, $subDir);
                if (!is_dir($realDir) || time() - filemtime($realDir) < 21600) {
                    continue;
                }
    
                LocalFile::deleteDir($realDir);
            }
        } catch (\Exception $e) {
            $this->logger->error("守卫进程执行异常：{$e->getMessage()}");
        }
    }

    /**
     * 归档 6 个月前的数据
     */
    private function fileData()
    {
        $optimize = false;
        $hour = date('H');
        if ($hour >= 2 && $hour <= 5) {
            $optimize = mt_rand(0, 100) > 90 ? true : false;
        }

        try {
            Container::get(ITaskRepository::class)->fileTask(time() - 86400 * 30 * 6, $optimize);
        } catch (\Exception $e) {
            $this->logger->error("守卫进程执行异常：{$e->getMessage()}");
        }
    }
}
