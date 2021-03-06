<?php

namespace Betterde\Logger\Jobs;

use Betterde\Logger\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Monolog\Handler\ElasticSearchHandler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Send documents
 *
 * Date: 2019/11/23
 * @author George
 * @package Betterde\Logger\Jobs
 */
class SendDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Log record
     *
     * @var array $records
     * Date: 2019/11/23
     * @author George
     */
    public $records = [];

    /**
     * Create a new job instance.
     *
     * @param array $records
     */
    public function __construct(array $records = [])
    {
        $this->records = $records;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var Logger $logger
         */
        $logger = app('betterde.logger');
        $handlers = $logger->getHandlers();

        /**
         * @var ElasticsearchHandler $handler
         */
        $handler = reset($handlers);

        if (config('logger.batch')) {
            if (count($this->records) > 0) {
                $handler->handleBatch($this->records);
            }
        } else {
            if (isset($this->records['message'])) {
                $handler->handle($this->records);
            }
        }
    }
}
