<?php

namespace Yaf\Support\Test;
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Yaf\Support\Auth\AuthManager;
use Yaf\Support\Foundation\Application;
use Yaf\Support\Http\Kernel;
use Yaf\Support\Http\Middleware\Authenticate;
use Yaf\Support\Http\Request;

class AuthMiddlewareTest
{
    public function testAuthIntoApplication()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        app()[AuthFactory::class] = function () {
            return new AuthManager();
        };

        var_dump(AuthFactory::class);
        var_dump(app(AuthFactory::class));
    }

    public function testAuthMiddleware()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        app()['request'] = function ($c) {
            return new Request('test/test', 'base_uri/test/test');
        };

        app()[Authenticate::class] = function ($c) {
            return new Authenticate();
        };

        app()[Kernel::class] = function ($c) {
            return new Kernel($c);
        };

        arrayConfig()->set('auth', require __DIR__ . '/../src/Auth/config/auth.php');

        $res = app(Kernel::class)->handle(app('request'));

        var_dump($res);
    }
}

$c = new AuthMiddlewareTest();
$c->testAuthMiddleware();