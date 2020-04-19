<?php
define('YAF_CONFIG_KEY', 'yaf_config_key');

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null $abstract
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function app($abstract = null)
    {
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
     * @return mixed|Yaf_Config_Abstract|Yaf_Config_Ini
     */
    function config($namespace = 'application')
    {
        $key = YAF_CONFIG_KEY . PATH_SEPARATOR . $namespace;
        try {
            $config = Yaf_Registry::get($key);
            if ($config == null) {
                $configPath = app()->getNamespaceConfigPath($namespace);
                $config     = Yaf_Application::app() == null ?
                    new Yaf_Config_Ini($configPath, ini_get('yaf.environ'))
                    : Yaf_Application::app()->getConfig();
                Yaf_Registry::set($key, $config);
            }
        } catch (Exception $exception) {
            return [];
        }

        return $config;
    }
}

