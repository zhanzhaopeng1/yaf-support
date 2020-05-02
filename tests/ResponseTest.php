<?php

namespace Yaf\Support\Test;

use Yaf\Support\Response\Response;

require __DIR__ . '/../vendor/autoload.php';

class ResponseTest
{
    public function testJsonReturn()
    {
        $response = new Response();
        $response->jsonReturn(['test' => 'test']);
    }
}

$t = new ResponseTest();
$t->testJsonReturn();