<?php

return [
    /*
     * Enable sending log of batch
     */
    'batch' => false,

    /*
     * Enable queue sending log
     */
    'queue' => false,

    /*
     * Log level
     */
    'level' => 200,

    /*
     * bubble
     */
    'bubble' => false,

    /*
     * Elasticsearch DB
     */
    'elasticsearch' => [
        'host' => env('ELASTICSEARCH_HOST', 'http://localhost'),
        'port' => env('ELASTICSEARCH_PORT', 9200)
    ],

    /*
     * Handler options
     */
    'options' => [
        'index' => 'monolog', // Elastic index name
        'type' => '_doc',    // Elastic document type
        'ignore_error' => false,     // Suppress Elasticsearch exceptions
    ]
];
