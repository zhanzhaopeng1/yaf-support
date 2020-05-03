<?php

namespace Yaf\Support\Foundation;

use Illuminate\Support\Arr;
use Pimple\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionParameter;
use Yaf\Support\Auth\AuthServiceProvider;
use Yaf\Support\Exceptions\InvalidParameterException;
use Psr\Container\ContainerInterface;
use Yaf\Support\Http\Middleware\MiddlewareServiceProvider;
use Yaf\Support\Validation\ValidationServiceProvider;

class Application extends Container implements ContainerInterface
{
    /**
     * The base path for the Laravel installation.
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * The config File Map namespace => file
     *
     * @var array
     */
    protected $configsFile = [
        'application' => 'application.ini',
        'database'    => 'database.ini',
    ];

    public function __construct(array $values = array(), $basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseServiceProviders();

        parent::__construct($values);
    }

    /**
     * @param $id
     * @return bool|object
     * @throws ReflectionException
     */
    public function build($id)
    {
        return $this->getInstance($this[$id]);
    }

    /**
     * @param $className
     * @return bool|object
     * @throws ReflectionException
     */
    public function getInstance($className)
    {
        // 实例化 ReflectionClass 对象
        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->isInstantiable()) {
            // 不能实例化的逻辑，抽象类和接口不能被实例化
            return false;
        }

        // 获取构造器
        $constructor = $reflectionClass->getConstructor();

        // 如果没有构造器，也就是没有依赖，直接实例化
        if (!$constructor) {
            return new $className;
        }

        // 如果有构造器，获取出构造器的参数
        $parameters = $constructor->getParameters();

        $dependencies = array_map(function ($parameter) {

            if (null == $parameter->getClass()) {
                return $this->processNoHinted($parameter);
            } else {
                return $this->processHinted($parameter);
            }
            /**
             * 这里递归的去寻找每一个参数类的依赖。例如第一次执行的时候，程序发现汽车Car类依赖底盘Chassis类
             */
            //return $this->getInstance($parameter->getClass()->getName());
        }, $parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * 没有类型提示的参数直接返回
     *
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws InvalidParameterException
     */
    private function processNoHinted(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        } else {
            throw new InvalidParameterException($parameter->getName() . 'not empty', 1);
        }
    }

    /**
     * 有类型提示的参数循环递归初始化参数类
     * @param ReflectionParameter $parameter
     * @return bool|object
     * @throws ReflectionException
     */
    private function processHinted(ReflectionParameter $parameter)
    {
        return $this->getInstance($parameter->getClass()->getName());
    }

    /**
     * Set the base path for the application.
     *
     * @param  string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this['path.base']   = $this->basePath();
        $this['path.config'] = $this->configPath();
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param  string $path Optionally, a path to append to the base path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param  string $path Optionally, a path to append to the config path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'configs' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Get the config file By namespace
     *
     * @param string $namespace
     * @return string
     */
    public function getNamespaceConfigPath($namespace = 'application')
    {
        return $this->configPath(Arr::get($this->configsFile, $namespace, ''));
    }

    /**
     * Register Base Provider
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new AuthServiceProvider());
        $this->register(new MiddlewareServiceProvider());
        $this->register(new ValidationServiceProvider());
    }

    /**
     * @param $serviceProvider
     */
    public function boot($serviceProvider)
    {
        $serviceProvider->boot();
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        return $this[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }


}