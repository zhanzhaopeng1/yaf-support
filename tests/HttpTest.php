<?php

namespace Yaf\Support\Test;

use SebastianBergmann\CodeCoverage\TestCase;
use Yaf\Support\Foundation\Application;
use Yaf\Support\Http\Kernel;
use Yaf\Support\Http\Middleware\Authenticate;
use Yaf\Support\Http\Middleware\SignMiddleware;
use Yaf\Support\Http\Request;

class HttpTest extends TestCase
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testApplication()
    {
        $application            = new Application();
        $application['request'] = function ($c) {
            return new Request('test/test', 'base_uri/test/test');
        };

        $this->assertInstanceOf(Request::class, $application->get('request'));
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function testHttpKernel()
    {
        $application            = new Application();
        $application['request'] = function ($c) {
            return new Request('test/test', 'base_uri/test/test');
        };

        $application[Authenticate::class] = function ($c) {
            return new Authenticate();
        };

        $application[SignMiddleware::class] = function ($c) {
            return new SignMiddleware();
        };

        $application[Kernel::class] = function ($c) {
            return new Kernel($c);
        };

        $res = $application->get(Kernel::class)->handle($application->get('request'));

        $this->assertInstanceOf(Request::class, $application->get('request'));
    }
}