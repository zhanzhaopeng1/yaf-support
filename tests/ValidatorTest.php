<?php

namespace Yaf\Support\Test;
require __DIR__ . '/../vendor/autoload.php';

use Yaf\Support\Foundation\Application;
use Yaf\Support\Http\Request;
use Yaf\Support\Http\Kernel;

class ValidatorTest
{
    /**
     * @throws \Yaf\Support\Validation\ValidationException
     */
    public function testValidator()
    {
        $app = new Application([], realpath(dirname(__FILE__)));
        \Yaf_Registry::set('app', $app);

        app()['request'] = function ($c) {
            $request = new Request('test/test', 'base_uri/test/test');
            $request->setShouldMethod('any');
            $request->setMiddleware(['auth']);
            $request->setParam('api_token', '12345678');
            $request->setParam('age', 12);
            $request->setParam('name', "23");

            return $request;
        };

        app()[Kernel::class] = function ($c) {
            return new Kernel($c);
        };

        arrayConfig()->set('auth', require __DIR__ . '/../src/Auth/config/auth.php');

        $res = app(Kernel::class)->handle(app('request'));

        validator()->validate([
            'age'  => 'required|int|between:1,100',
            'name' => 'required|string'
        ]);

        $age  = request()->getParam('age');
        $name = request()->getParam('name', 'test');
        var_dump($age);
        var_dump($name);
    }
}

$v = new ValidatorTest();
$v->testValidator();