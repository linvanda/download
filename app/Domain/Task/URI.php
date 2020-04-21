<?php

namespace App\Domain\Task;

use App\ErrCode;
use WecarSwoole\Exceptions\Exception;

class URI
{
    public const TYPE_HTTP = 'http';

    protected $type;
    protected $url;

    public function __construct(string $url)
    {
        $this->setType($url);
        $this->setUrl($url);
    }

    public function url(): string
    {
        return $this->url;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * 目前进支持 http，如果有其他协议，请创建子类处理
     */
    protected function setType(string $url)
    {
        if (strpos($url, 'http') !== 0) {
            throw new Exception("暂不支持的协议类型");
        }

        $this->type = self::TYPE_HTTP;
    }

    protected function setUrl(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("Url格式不合法", ErrCode::PARAM_VALIDATE_FAIL);
        }

        $this->url = $url;
    }
}
