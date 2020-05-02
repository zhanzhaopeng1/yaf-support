<?php

namespace Yaf\Support\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\NullHandler;
use Yaf\Support\Log\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @method static log($message, array $context = [])
 * @method static debug($message, array $context = [])
 * @method static info($message, array $context = [])
 * @method static notice($message, array $context = [])
 * @method static warning($message, array $context = [])
 * @method static error($message, array $context = [])
 *
 * Class Log
 * @package Yaf\Support\Log
 */
class Log
{
    /**
     * @var Logger
     */
    protected static $logger;

    /**
     * @return Logger
     */
    public static function createLogger()
    {
        date_default_timezone_set("PRC");

        if (isset(self::$logger)) {
            return self::$logger;
        }

        $logger = new Logger(arrayConfig()->logging->default);
        if (empty($handlers = arrayConfig()->logging->channels->handlers->toArray())) {
            $logger->pushHandler(new NullHandler());
        } else {
            foreach ($handlers as $handler) {
                $logger->pushHandler(self::createHandler($handler));
            }
        }

        self::$logger = $logger;

        return $logger;
    }

    /**
     * @param array $config
     * @return BufferHandler|FilterHandler|StreamHandler
     */
    protected static function createHandler(array $config)
    {
        $minLevel = Logger::toMonologLevel($config['minLevel']);
        $maxLevel = Logger::toMonologLevel($config['maxLevel']);

        $handler = new StreamHandler($config['file'], $config['cliBufferLimit']);

        // 设置规范
        if ($config['formatter']) {
            $handler->setFormatter(new LineFormatter($config['formatter'], 'Y-m-d H:i:s'));
        }

        $bufferLimit = request()->isCli() ? $config['cliBufferLimit'] : $config['bufferLimit'];

        // 支持 buffer
        if ($bufferLimit) {
            $handler = new BufferHandler($handler, $bufferLimit, Logger::DEBUG, true, true);
        }

        // 错误定义范围
        if ($minLevel) {
            $handler = new FilterHandler($handler, $minLevel, $maxLevel);
        }

        // 添加 processor 实例
        foreach ($config['processors'] ?? [] as $processorClass) {
            $handler->pushProcessor(new $processorClass());
        }

        return $handler;
    }

    /**
     * @param $method
     * @param $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        self::createLogger()->$method(...$arguments);
    }

}