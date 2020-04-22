<?php

namespace Yaf\Support\Test;
require __DIR__ . '/../vendor/autoload.php';

use Yaf\Support\Foundation\Application;

class ConfigTest
{
    public function testConfig()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        var_dump(config()->test->test);
    }

    public function testDBConfig()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        var_dump(config('database')->db->payday->host);
    }

    public function testSetConfig()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        config()->test->test;

        $res = arrayConfig();
        $res->set('auth', [
            'api' => [
                'driver'   => 'token',
                'provider' => 'users',
                'hash'     => false,
            ]
        ]);

        $res->set('auth123', [
            'api' => [
                'driver'   => 'token',
                'provider' => 'users',
                'hash'     => false,
            ]
        ]);

        var_dump($res->get('auth123')->toArray());
    }

    public function testSetConfig1()
    {
        $res = arrayConfig();
        $res->set('auth', [
            'api' => [
                'driver'   => 'token',
                'provider' => 'users',
                'hash'     => false,
            ]
        ]);

        arrayConfig()->auth->api->set('driver','12345');

        var_dump(arrayConfig()->auth);
    }
}

$c = new ConfigTest();
$c->testSetConfig1();