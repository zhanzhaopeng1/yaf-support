<?php

namespace Yaf\Support\Log;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yaf\Support\Foundation\Application;

class ServiceProvider implements ServiceProviderInterface
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
        $this->app = $pimple;
    }

    public function boot()
    {
        arrayConfig()->set('logging', require __DIR__ . '/../config/Logging.php');
    }
}
