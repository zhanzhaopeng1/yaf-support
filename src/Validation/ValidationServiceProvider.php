<?php

namespace Yaf\Support\Validation;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yaf\Support\Foundation\Application;

class ValidationServiceProvider implements ServiceProviderInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $this->app               = $pimple;
        $this->app['validator'] = function () {
            return new Validator();
        };
    }
}
