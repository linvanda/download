<?php

namespace App\Processor;

use App\Bootstrap;
use App\Domain\Task\ITaskRepository;
use App\Foundation\File\LocalFile;
use App\Processor\Monitor\QueueMonitor;
use App\Processor\Monitor\TaskRetry;
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
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function run($arg)
    {
        Bootstrap::boot();

        $this->logger = Container::get(LoggerInterface::class);
        $this->logger->info('启动守卫程序');

        // 只有主服才执行的逻辑
        if (in_array(Config::getInstance()->getConf('master_server'), swoole_get_local_ip())) {
            $this->masterDefender();
        }
        
        // 30 分钟一次，清理目录中的无用文件
        Timer::tick(1800000, Closure::fromCallable([$this, 'clearDir']));
    }

    public function onShutDown()
    {
        // nothing
    }

    public function onReceive(string $str)
    {
        // nothing
    }

    private function masterDefender()
    {
        // 15 秒一次，执行失败重试
        Timer::tick(15000, Closure::fromCallable([TaskRetry::getInstance(), 'watch']));
        // 30 秒一次，检测队列状态
        Timer::tick(30000, Closure::fromCallable([QueueMonitor::getInstance(), 'watch']));
        // 3 小时一次，归档 task 数据
        Timer::tick(10800000, Closure::fromCallable([$this, 'fileData']));
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
            $this->logger->error("守卫进程执行异常。msg:{$e->getMessage()},trace:" . $e->getTraceAsString());
        }
    }

    /**
     * 归档 6 个月前的数据
     */
    private function fileData()
    {
        $hour = intval(date('G'));

        // 只在晚上 0 - 6 点处理
        if ($hour > 6) {
            return;
        }

        $optimize = mt_rand(0, 100) > 95 ? true : false;

        try {
            Container::get(ITaskRepository::class)->fileTask(time() - 86400 * 30 * 6, $optimize);
        } catch (\Exception $e) {
            $this->logger->error("守卫进程执行异常。msg:{$e->getMessage()},trace:" . $e->getTraceAsString());
        }
    }
}
