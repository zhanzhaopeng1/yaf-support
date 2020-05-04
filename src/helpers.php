<?php
define('YAF_CONFIG_KEY', 'yaf_config_key');

use Illuminate\Support\Str;
use Yaf\Support\Database\Database;
use Yaf\Support\Foundation\Application;
use Yaf\Support\Response\Response;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null $abstract
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function app($abstract = null)
    {
        if (Yaf_Registry::get('app') === null) {
            Yaf_Registry::set('app', new Application([], realpath(dirname(__FILE__))));
        }

        if (is_null($abstract)) {
            return Yaf_Registry::get('app');
        }

        return Yaf_Registry::get('app')->get($abstract);
    }
}


if (!function_exists('config')) {

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param string $namespace
     * @param string $configFilePath
     * @return mixed|Yaf_Config_Abstract|Yaf_Config_Ini|Yaf_Config_Simple
     */
    function config($namespace = 'application')
    {
        $key = YAF_CONFIG_KEY . PATH_SEPARATOR . $namespace;
        try {
            $config = Yaf_Registry::get($key);
            if ($config == null) {
                $configPath = app()->getNamespaceConfigPath($namespace);
                if (Str::contains($configPath, 'ini')) {
                    $config = new Yaf_Config_Ini($configPath, ini_get('yaf.environ'));
                } else {
                    $config = new Yaf_Config_Simple([]);
                }
                Yaf_Registry::set($key, $config);
            }
        } catch (Exception $exception) {
            return [];
        }

        return $config;
    }
}

if (!function_exists('arrayConfig')) {

    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param string $namespace
     * @return Yaf_Config_Simple
     */
    function arrayConfig($namespace = 'thirdParty')
    {
        return config($namespace);
    }
}

if (!function_exists('dbConnect')) {

    /**
     * @param string $name
     * @return mixed|\Yaf\Support\Database\PdoClient
     * @throws Exception
     */
    function dbConnect($name = 'mysql')
    {
        $key = __METHOD__ . $name;
        $pdo = Yaf_Registry::get($key);
        if (!$pdo) {
            $pdo = (new Database())->connect($name);
            Yaf_Registry::set($key, $pdo);
        }

        return $pdo;
    }
}

if (!function_exists('Auth')) {

    /**
     * @method int|null id()
     * @method \Illuminate\Contracts\Auth\Authenticatable|null user()
     *
     * @see \Yaf\Support\Auth\AuthManager
     * @see \Yaf\Support\Auth\TokenGuard
     *
     * @return \Yaf\Support\Auth\TokenGuard
     */
    function Auth()
    {
        return app(\Illuminate\Contracts\Auth\Factory::class);
    }
}

if (!function_exists('request')) {

    /**
     * @return \Yaf\Support\Http\Request
     */
    function request()
    {
        return app('request');
    }
}

if (!function_exists('validator')) {

    /**
     * @return \Yaf\Support\Validation\Validator
     */
    function validator()
    {
        return app('validator');
    }
}

if (!function_exists('log_path')) {

    /**
     * @param $path
     * @return string
     */
    function log_path($path)
    {
        return config()->log->dir . DIRECTORY_SEPARATOR . $path;
    }
}