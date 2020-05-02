<?php

namespace Yaf\Support\Test;

use Yaf\Support\Foundation\Application;
use Yaf\Support\Http\Request;
use Yaf\Support\Log\ServiceProvider;
use Yaf\Support\Response\Response;

require __DIR__ . '/../vendor/autoload.php';

class ResponseTest
{
    public function testJsonReturn()
    {
        date_default_timezone_set("PRC");

        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        app()['request'] = function ($c) {
            return new Request('cli', 'cli');
        };

        (new ServiceProvider())->boot();

        $response = new Response();
        $response->jsonReturn(['test' => 'test']);
    }
}

$t = new ResponseTest();
$t->testJsonReturn();