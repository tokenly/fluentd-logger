<?php

namespace Tokenly\FluentdLogger;

use Fluent\Logger\FluentLogger;
use Illuminate\Contracts\Logging\Log as LogContract;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Tokenly\FluentdLogger\FluentMonologHandler;
use Tokenly\FluentdLogger\FluentSlackLogger;

class FluentdLoggerServiceProvider extends ServiceProvider
{

    public function register()
    {
        /**
         * for package configure
         */
        $configPath = __DIR__ . '/config/fluent.php';
        $this->mergeConfigFrom($configPath, 'fluent');
        $this->publishes([$configPath => config_path('fluent.php')], 'log');

        // bind the fluent logger
        $this->app->singleton('fluent.measurements', function ($app) {
            $config = $app['config']->get('fluent');

            if (!$config['enabled']) {
                return null;
            }

            $fluent_logger = new FluentLogger($config['host'], $config['port'], $config['options']);
            // Prefix all tags with {{app_code}}.{{env}}
            return new FluentEventLogger($fluent_logger, 'measure.'.$config['app_code'].'.'.$app['env']);
        });


        // add a slack handler (slack.*)
        $this->app->singleton('fluent.slack', function ($app) {
            $config = $app['config']->get('fluent');

            if (!$config['enabled']) {
                return null;
            }

            $fluent_logger = new FluentLogger($config['host'], $config['port'], $config['options']);
            return new FluentSlackLogger($fluent_logger, 'slack.'.$config['app_code'].'.'.$app['env']);
        });

        // register monolog handler (applog.*)
        $config = $this->app['config']->get('fluent');
        if ($config['enabled']) {
            if (interface_exists(LogContract::class)) {
                // Laravel 5.5 and below
                $monolog = app(LogContract::class)->getMonolog();
            } else {
                // Laravel 5.6+
                $monolog = app(LoggerInterface::class)->driver();
            }
            $fluent_logger = new FluentLogger($config['host'], $config['port'], $config['options']);
            $tag = 'applog.'.$config['app_code'].'.'.$this->app['env'];
            return $monolog->pushHandler(
                new FluentMonologHandler($fluent_logger, $tag, $config['applog.level'])
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function compiles()
    {
        return [
            base_path() . '/tokenly/fluentd-logger/src/FluentdLoggerServiceProvider.php',
            base_path() . '/tokenly/fluentd-logger/src/FluentEventLogger.php',
            base_path() . '/tokenly/fluentd-logger/src/FluentMonologHandler.php',
            base_path() . '/tokenly/fluentd-logger/src/helpers.php',
            base_path() . '/vendor/fluent/logger/src/Entity.php',
            base_path() . '/vendor/fluent/logger/src/Exception.php',
            base_path() . '/vendor/fluent/logger/src/FluentLogger.php',
            base_path() . '/vendor/fluent/logger/src/JsonPacker.php',
            base_path() . '/vendor/fluent/logger/src/LoggerInterface.php',
            base_path() . '/vendor/fluent/logger/src/PackerInterface.php',
        ];
    }

}