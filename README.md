# Fluentd Logger

Sends application log messages and events to a fluentd log shipper.

# Installation


### Add the package via composer

```
composer require tokenly/fluentd-logger
```

## Usage with Laravel

### Add the Service Provider

Add the following to the `providers` array in your application config:

```
Tokenly\FluentdLogger\FluentdLoggerServiceProvider::class,
```

### Set the environment variables

```
APP_CODE=myapp
FLUENTD_ENABLED=true
FLUENTD_APPLOG_LEVEL=DEBUG

# this makes the default Laravel monolog handler very quiet to not fill up the hard drive
APP_LOG_LEVEL=EMERGENCY

# for a local fluentd instance (recommended)
FLUENTD_SOCKET=/tmp/fluentd.sock

# if using a remote fluentd server (not recommended)
# FLUENTD_HOST=http://some.host
# FLUENTD_PORT=24224
```


### Standard Log Events

Normal log events are sent to fluentd using standard Laravel logging functions

```php
Illuminate\Support\Facades\Log::info("hello world");
```

### Measurement Events

To measure an event, use `fluent_measure($event, $data=[], $tags=null);`

```php
fluent_measure('widget.created', ['widgets' => 4], ['username' => 'leroy']);
```

$data should contain numeric data.  Think of $tags as additional indexes for that data.  A timestamp is included by default.



## Usage without Laravel

```php
$fluent_logger = new \Tokenly\FluentdLogger\FluentLogger($host, $port);

# set a tag prefix
$app_code = 'myapp';
$environment = 'production';
$tag = 'applog.'.$app_code.'.'.$environment;

# set up monolog
$monolog->pushHandler(new \Tokenly\FluentdLogger\FluentMonologHandler($fluent_logger, $tag));

# set up fluent event logger for measurements
$measurement_logger = new \Tokenly\FluentdLogger\FluentEventLogger($fluent_logger, 'measure.'.$app_code.'.'.$environment);


# use monolog
$monolog->info("hi world");

# use measurements
$measurement_logger->log('widget.created', ['widgets' => 4], ['username' => 'leroy']);

```

