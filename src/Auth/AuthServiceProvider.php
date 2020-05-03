<?php

namespace Yaf\Support\Auth;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yaf\Support\Foundation\Application;
use Illuminate\Contracts\Auth\Factory;

class AuthServiceProvider implements ServiceProviderInterface
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
        $this->registerAuthenticator();
    }

    public function boot()
    {
        arrayConfig()->set('auth', require __DIR__ . '/config/auth.php');
    }

    /**
     * Register the authenticator services.
     *
     * @return void
     */
    protected function registerAuthenticator()
    {
        $this->app[Factory::class] = function () {

            return new AuthManager();
        };
    }
}
