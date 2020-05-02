<?php

namespace Yaf\Support\Test;

use Yaf\Support\Foundation\Application;
use Yaf\Support\Http\Request;
use Yaf\Support\Log\Log;
use Yaf\Support\Log\ServiceProvider;

require __DIR__ . '/../vendor/autoload.php';

class MongoTest
{
    public function testLog()
    {
        date_default_timezone_set("PRC");

        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        (new ServiceProvider())->boot();

        Log::debug('debug log');
        Log::info('info log');
        Log::notice('notice log');
        Log::error('error log');
        Log::warning('warring log');

    }

    public function testCronLog()
    {
        date_default_timezone_set("PRC");

        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        app()['request'] = function ($c) {
            return new Request('cli', 'cli');
        };

        (new ServiceProvider())->boot();

        Log::debug('debug log');
        Log::info('info log');
        Log::notice('notice log');
        Log::error('error log');
        Log::warning('warring log');

        Log::debug('debug log1');
        Log::info('info log1');
        Log::notice('notice log1');
        Log::error('error log1');
        Log::warning('warring log1');

        Log::debug('debug log1');
        Log::info('info log1');
        Log::notice('notice log1');
        Log::notice('notice log1....');
        Log::error('error log1');
        Log::warning('warring log1');

        sleep(2);

    }
}

$t = new MongoTest();
$t->testCronLog();