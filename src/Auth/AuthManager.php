<?php

namespace Yaf\Support\Auth;

use Illuminate\Contracts\Auth\Factory as FactoryContract;
use InvalidArgumentException;

class AuthManager implements FactoryContract
{
    use CreatesUserProviders;

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
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

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
        $name = $name ?: $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }

    /**
     * Resolve the given guard.
     *
     * @param  string $name
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
        }

        if (isset($this->customCreators[$config->driver])) {
            return $this->callCustomCreator($name, $config);
        }

        $driverMethod = 'create' . ucfirst($config->driver) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($name, $config);
        }

        throw new InvalidArgumentException(
            "Auth driver [{$config->driver}] for guard [{$name}] is not defined."
        );
    }

    /**
     * Call a custom driver creator.
     *
     * @param  string $name
     * @param  array  $config
     * @return mixed
     */
    protected function callCustomCreator($name, array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $name, $config);
    }

    /**
     * Create a token based authentication guard.
     *
     * @param string $name
     * @param  array $config
     * @return TokenGuard
     * @throws \Exception
     */
    public function createTokenDriver($name, $config)
    {
        // The token guard implements a basic API token based guard implementation
        // that takes an API token field from the request and matches it to the
        // user in the database or another persistence layer where users are.
        $guard = new TokenGuard(
            $this->createUserProvider($config['provider'] ?? null),
            $this->app['request'],
            $config['input_key'] ?? 'api_token',
            $config['storage_key'] ?? 'api_token',
            $config['hash'] ?? false
        );

        return $guard;
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
     * Get the guard configuration.
     *
     * @param  string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return arrayConfig()->auth->guards->{$name};
    }

    /**
     * Get the default authentication driver name.
     *
     * @return string
     */
    protected function getDefaultDriver()
    {
        return arrayConfig()->auth->defaults->guard;
    }

}