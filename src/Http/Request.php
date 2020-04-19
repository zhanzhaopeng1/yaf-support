<?php

namespace Yaf\Support\Http;

class Request extends \Yaf_Request_Http
{
    protected $sign = '';

    /**
     * @return string
     */
    public function getSign(): string
    {
        return $this->sign;
    }

    /**
     * @param string $sign
     */
    public function setSign(string $sign): void
    {
        $this->sign = $sign;
    }
}