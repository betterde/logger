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
        ], 'logger');
    }

    /**
     * Date: 2019/11/24
     * @author George
     */
    public function register()
    {
        $this->app->singleton('betterde.logger', function () {
            $urls = $this->hostsGenerator();
            $client = ClientBuilder::create()->setHosts($urls)->setRetries(config('logger.elasticsearch.retries'))->build();
            return new Logger(config('logging.default'), [
                new ElasticsearchHandler($client, config('logger.options'), config('logger.level'), config('logger.bubble'))
            ]);
        });
    }

    /**
     * Elasticsearch hosts generator
     *
     * Date: 2019/11/24
     * @return array
     * @author George
     */
    private function hostsGenerator(): array
    {
        $urls = [];
        $hosts = config('logger.elasticsearch.hosts');

        foreach ($hosts as $host) {
            $certificate = sprintf('%s:%s@', $host['user'], $host['pass']);
            $urls[] = sprintf('%s::/%s%s:%s', $host['scheme'], $certificate, $host['host'], $host['port']);
        }

        return $hosts;
    }
}
