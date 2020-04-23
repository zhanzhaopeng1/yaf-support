<?php

namespace Yaf\Support\Http;

use Yaf_Request_Http;

class Request extends Yaf_Request_Http
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
     * @var string
     */
    protected $shouldMethod = 'post';

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
        if ($rawInput) {
            $this->rawInput = $rawInput;
        } else {
            $this->rawInput = file_get_contents('php://input');
        }
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

    /**
     * @return string
     */
    public function getShouldMethod(): string
    {
        return $this->shouldMethod;
    }

    /**
     * @param string $shouldMethod
     */
    public function setShouldMethod(string $shouldMethod): void
    {
        $this->shouldMethod = $shouldMethod;
    }

}