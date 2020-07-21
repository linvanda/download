<?php

namespace App\Processor;

use App\Bootstrap;
use App\Foundation\File\LocalFile;
use Closure;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Config;
use Swoole\Timer;
use WecarSwoole\Util\File;

/**
 * 后台守卫程序，执行失败重试、数据归档的任务
 * Class Defender
 */
class Defender extends AbstractProcess
{
    public function run($arg)
    {
        Bootstrap::boot();

        // 10 秒一次，执行失败重试
        Timer::tick(10000, Closure::fromCallable([$this, 'retryTask']));
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

    private function retryTask()
    {
        
    }

    /**
     * 清理最后修改时间是 6 小时前的目录
     */
    private function clearDir()
    {
        $dir = Config::getInstance()->getConf('local_file_base_dir');

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
    }

    /**
     * 归档 6 个月前的数据
     */
    private function fileData()
    {

    }
}
