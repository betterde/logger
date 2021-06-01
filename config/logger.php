<?php

return [
    /*
     * Enable sending log of batch
     */
    'batch' => false,

    /*
     * Enable queue sending log
     */
    'queue' => [
        'enable' => false,
        'name' => env('LOG_QUEUE_NAME', 'logging')
    ],

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
        'hosts' => [
            [
                /*
                 * host is required
                 */
                'host' => env('ELASTICSEARCH_HOST', 'localhost'),
                'port' => env('ELASTICSEARCH_PORT', 9200),
                'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
                'user' => env('ELASTICSEARCH_USER', null),
                'pass' => env('ELASTICSEARCH_PASS', null)
            ],
        ],
        'retries' => 2,
        /*
         * Cart path
         */
        'cert' => '',
        /*
         * set timeout and connect_timeout
         */
        'params' => [
            'client' => [
                'timeout' => 2,
                'connect_timeout' => 2
            ]
        ]
    ],

    /*
     * Handler options
     */
    'options' => [
        'index' => strtolower(env('APP_NAME', 'laravel')), // Elastic index name
        'type' => '_doc',    // Elastic document type
        'ignore_error' => false,     // Suppress Elasticsearch exceptions
    ],

    /*
     * Enable trace of exception log
     */
    'exception' => [
        'trace' => false,
    ],

    /*
     * Log extra filed
     */
    'extra' => [
        'host' => env('APP_URL'),
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'env' => env('APP_ENV')
    ]
];
