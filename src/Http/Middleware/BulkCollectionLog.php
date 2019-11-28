<?php

namespace Betterde\Logger\Http\Middleware;

use Closure;
use Betterde\Logger\Logger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Betterde\Logger\Jobs\SendDocuments;
use Betterde\Logger\Handler\ElasticsearchHandler;

class BulkCollectionLog
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Send bulk log
     *
     * Date: 2019/11/24
     * @param Request $request
     * @param Response $response
     * @author George
     */
    public function terminate($request, $response)
    {
        /**
         * @var Logger $logger
         */
        $logger = app('betterde.logger');
        if (empty($logger->records)) {
            return;
        }

        if (config('logger.queue.enable')) {
            SendDocuments::dispatch($logger->records)->onQueue(config('logger.queue.name'));
        } else {
            $handlers = $logger->getHandlers();

            /**
             * @var ElasticsearchHandler $handler
             */
            $handler = reset($handlers);
            $handler->handleBatch($logger->records);
        }
    }
}
