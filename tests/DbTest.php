<?php

namespace Yaf\Support\Test;

use Illuminate\Contracts\Auth\Factory;
use Yaf\Support\Database\PdoClient;
use Yaf\Support\Foundation\Application;

require __DIR__ . '/../vendor/autoload.php';

class DbTest
{
    /**
     * @throws \Exception
     */
    public function testDB()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        $condition = PdoClient::condition('username=? and deleted_at is null', 'test');

        $res = dbConnect()->getRowByCondition('user', $condition);

        var_dump($res);
    }
}

$r = new DbTest();
$r->testDB();