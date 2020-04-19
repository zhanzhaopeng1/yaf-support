<?php

namespace Yaf\Support\Auth;

use Illuminate\Contracts\Auth\Factory as FactoryContract;

class AuthManager implements FactoryContract
{
    /**
     * The application instance
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $app;

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $guards = [];

    /**
     * AuthManager constructor.
     */
    public function __construct()
    {
        $this->app = app();
    }

    /**
     * Get a guard instance by name.
     *
     * @param  string|null $name
     * @return mixed
     */
    public function guard($name = null)
    {

    }

    /**
     * Set the default guard the factory should serve.
     *
     * @param  string $name
     * @return void
     */
    public function shouldUse($name)
    {
        // TODO: Implement shouldUse() method.
    }

    /**
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return config()->auth->default->driver;
    }

}