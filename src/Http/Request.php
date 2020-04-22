<?php

namespace Yaf\Support\Http;

class Request extends \Yaf_Request_Http
{
    /**
     * @var array request middleware
     */
    protected $middleware = [];

    /**
     * @var string
     */
    protected $rawInput = '';

    /**
     * @var string
     */
    protected $queryString = '';

    /**
     * @var string
     */
    protected $sign = '';

    /**
     * @return string
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param array $middleware
     */
    public function setMiddleware(array $middleware): void
    {
        $this->middleware = $middleware;
    }

    /**
     * @return string
     */
    public function getRawInput(): string
    {
        return $this->rawInput;
    }

    /**
     * @param string $rawInput
     */
    public function setRawInput(string $rawInput): void
    {
        $this->rawInput = $rawInput;
    }

    /**
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /**
     * @param string $queryString
     */
    public function setQueryString(string $queryString): void
    {
        $this->queryString = $queryString;
    }

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