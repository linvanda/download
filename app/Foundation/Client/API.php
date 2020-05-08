<?php

namespace App\Foundation\Client;

use WecarSwoole\Client\API as BaseAPI;

class API
{
    private $tryNum = 0;

    /**
     * 如果调用失败，则重试 3 次，每次重试间隔逐渐拉长
     */
    public function invoke(string $url, string $method = 'GET', array $params = [])
    {
        $result = BaseAPI::simpleInvoke($url, $method, $params);
        
    }
}
