<?php

return [
    'app_code' => env('APP_CODE', 'app'),
    'enabled' => !!env('FLUENTD_ENABLED', false),
    'host' => env('FLUENTD_SOCKET', null) ? 'unix://' . env('FLUENTD_SOCKET') : env('FLUENTD_HOST', '127.0.0.1'),
    'port' => env('FLUENTD_PORT', 24224), // this ignored if FLUENTD_HOST begins with unix://
    'options' => [],
    'applog.level' => Monolog\Logger::toMonologLevel(env('FLUENTD_APPLOG_LEVEL', 'DEBUG')),
    'use_fluent_bit' => env('FLUENTD_USE_FLUENT_BIT', false),
];
