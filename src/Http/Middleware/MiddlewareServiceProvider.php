<?php

namespace Yaf\Support\Http\Middleware;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MiddlewareServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['auth'] = function () {
            return new Authenticate();
        };

        $pimple['sign'] = function () {
            return new SignMiddleware();
        };

        $pimple['router'] = function () {
            return new RouterMiddleware();
        };
    }
}