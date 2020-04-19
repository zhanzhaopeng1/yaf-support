<?php

namespace Yaf\Support\Test;
require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Yaf\Support\Foundation\Application;

class ConfigTest extends TestCase
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
}