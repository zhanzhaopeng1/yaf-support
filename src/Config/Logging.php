<?php

use Psr\Log\LogLevel;

return [

    /*
   |--------------------------------------------------------------------------
   | Default Log Channel
   |--------------------------------------------------------------------------
   |
   | This option defines the default log channel that gets used when writing
   | messages to the logs. The name specified in this option should match
   | one of the channels defined in the "channels" configuration array.
   |
   */
    'default'  => 'stack',
    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */
    'channels' => [
        'handlers' => [
            [
                // 日志文件名
                'file'           => log_path('{date:Ymd}/log_' . config()->APP_NAME . '_app_{date:YmdH}.log'),
                // 日志 level 范围
                'minLevel'       => LogLevel::DEBUG,
                'maxLevel'       => LogLevel::NOTICE,
                // 打开缓冲
                'bufferLimit'    => 128,
                // cli 模式下的日志 buffer
                'cliBufferLimit' => 5,
                // 日志格式化规范
                'formatter'      => "[%datetime%] %level_name%: %message% %context% %extra%\n",
                // 日志处理器
                'processors'     => [
                ],
            ],
            [
                // 日志文件名
                'file'           => log_path('{date:Ymd}/log_' . config()->APP_NAME . '_app_error_{date:YmdH}.log'),
                // 日志 level 范围
                'minLevel'       => LogLevel::WARNING,
                'maxLevel'       => LogLevel::ERROR,
                // 打开缓冲
                'bufferLimit'    => 128,
                // cli 模式下的日志 buffer
                'cliBufferLimit' => 5,
                // 日志格式化规范
                'formatter'      => "[%datetime%] %level_name%: %message% %context% %extra%\n",
                // 日志处理器
                'processors'     => [
                ],
            ],
        ],
    ]

];