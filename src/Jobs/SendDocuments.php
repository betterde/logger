<?php

namespace Betterde\Logger\Jobs;

use Betterde\Logger\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Betterde\Logger\Handler\ElasticsearchHandler;

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
        /**
         * @var ElasticsearchHandler $handler
         */
        $handler = $logger->popHandler();
        if (count($this->records) > 0) {
            $handler->handle($this->records);
        } else {
            $handler->handleBatch($logger->records);
        }
    }
}
