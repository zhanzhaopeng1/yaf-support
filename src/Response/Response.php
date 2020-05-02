<?php

namespace Yaf\Support\Response;

use Yaf_Response_Http;

class Response extends Yaf_Response_Http
{
    public function jsonReturn(array $data, string $message = 'success', int $code = 0, int $httpCode = 200)
    {
        $content = [
            'code'    => $code,
            'data'    => $data,
            'message' => $message
        ];

        $this->setHeader('Content-Type', 'application/json', false, $httpCode);
        $this->setBody(json_encode($content));
        $this->response();
    }
}