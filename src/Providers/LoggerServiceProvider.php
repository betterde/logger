<?php

namespace Betterde\Logger\Providers;

use Betterde\Logger\Logger;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Betterde\Logger\Handler\ElasticSearchHandler;

/**
 * Date: 2019/11/23
 * @author George
 * @package Betterde\Logger\Providers
 */
class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Date: 2019/11/24
     * @author George
     */
    public function boot()
    {
        /**
         * 发布配置文件
         */
        $this->publishes([
            __DIR__ . '/../../config/logger.php' => config_path('logger.php'),
        ], 'betterde.logger');
    }

    /**
     * Date: 2019/11/24
     * @author George
     */
    public function register()
    {
        $this->app->singleton('betterde.logger', function () {
            $client = ClientBuilder::create()->setHosts(config('logger.elasticsearch.hosts'))->setRetries(config('logger.elasticsearch.retries'))->build();
            return new Logger('elastic', [
                new ElasticsearchHandler($client, config('logger.options'), config('logger.level'), config('logger.bubble'))
            ]);
        });
    }
}
