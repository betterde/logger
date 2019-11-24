<?php

namespace Betterde\Logger\Providers;

use Betterde\Logger\Logger;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Betterde\Logger\Handler\ElasticsearchHandler;

/**
 * Date: 2019/11/23
 * @author George
 * @package Betterde\Logger\Providers
 */
class LoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /**
         * 发布配置文件
         */
        $this->publishes([
            __DIR__ . '/../../config/logger.php' => config_path('logger.php'),
        ], 'logger');
    }

    public function register()
    {
        $this->app->singleton('logger', function () {
            $client = ClientBuilder::create()->setHosts([''])->build();
            return new Logger('logger', [
                new ElasticsearchHandler($client, config('logger.options'), config('logger.level'), config('logger.bubble'))
            ]);
        });
    }
}
