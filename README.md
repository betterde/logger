# Laravel log driver for elasticsearch

# Installation

```bash
$ composer require betterde/logger
$ php artisan vendor:publish --tag=betterde.logger
```
# Config

You can modify config in `config/logger.php`.

Now we can add the `channel` of `channels` in `config/logging.php` file.

```php
'channels' => [
	.
	.
	.
    'elastic' => [
        'driver' => 'custom',
        'via' => Betterde\Logger\ElasticsearchLogger::class,
    ],
],
```
Add the `\Betterde\Logger\Http\Middleware\BulkCollectionLog` middleware to `App\Http\Kernel.php` file.

```php
/**
 * The application's global HTTP middleware stack.
 *
 * These middleware are run during every request to your application.
 *
 * @var array
 */
protected $middleware = [
    \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
    \App\Http\Middleware\TrimStrings::class,
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    \App\Http\Middleware\TrustProxies::class,
    \Betterde\Logger\Http\Middleware\BulkCollectionLog::class,
];
```

Now define the environment variable in `.env` file like this:

```
LOG_CHANNEL=elastic
ELASTICSEARCH_HOST=localhost
ELASTICSEARCH_PORT=9200
ELASTICSEARCH_SCHEME=http
ELASTICSEARCH_USER=
ELASTICSEARCH_PASS=
ELASTICSEARCH_LOG_INDEX_PREFIX=laravel
```

Finally, I hope this is helpful.
