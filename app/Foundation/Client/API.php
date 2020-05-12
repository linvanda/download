<?php

namespace App\Foundation\Client;

use App\ErrCode;
use Psr\Log\LoggerInterface;
use WecarSwoole\Client\API as BaseAPI;
use WecarSwoole\Client\Response;
use WecarSwoole\Exceptions\Exception;
use Swoole\Coroutine as Co;
use WecarSwoole\Container;

class API
{
    private const MAX_RETRY_NUM = 3;

    private $retryNum = 0;
    private $lastErrNo = 0;
    private $lastErrMsg = '';
    private $url;
    private $method;

    public function __construct(string $url = '', string $method = 'GET')
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * 如果调用失败，则最多重试 3 次，每次重试间隔逐渐拉长
     * 注意：此处的重试对于当前协程来说是阻塞的，会发生协程切换
     * @return array
     */
    public function invoke(array $params): array
    {
        $this->retryNum = 0;

        $result = $this->retryCall($params);
        if (!$result || $result->getStatus() >= 300) {
            throw new Exception("url 请求失败：{$this->url}。errno:{$this->lastErrNo},errmsg:{$this->lastErrMsg}", ErrCode::FETCH_SOURCE_FAILED);
        }

        return $result->getBody();
    }

    private function retryCall(array $params): Response
    {
        $result = null;
        while ($this->retryNum++ < self::MAX_RETRY_NUM) {
            try {
                $result = BaseAPI::simpleInvoke($this->url, $this->method, $params);

                if ($result && $result->getStatus() >= 200 && $result->getStatus() < 300) {
                    $this->lastErrNo = 0;
                    $this->lastErrMsg = '';
                    return $result;
                }
            } catch (\Exception $e) {
                $result = new Response([], $e->getCode(), $e->getMessage());
            }

            $this->lastErrNo = $result->getStatus();
            $this->lastErrMsg = $result->getMessage();

            Co::sleep($this->calcIntervalTime());
            Container::get(LoggerInterface::class)->warning("第{$this->retryNum}次重试{$this->url}，原因：{$this->lastErrMsg}");
        }

        return $result;
    }

    private function calcIntervalTime(): int
    {
        return pow($this->retryNum, 3) * 5;
    }
}