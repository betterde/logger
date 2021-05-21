<?php declare(strict_types=1);

namespace Betterde\Logger\Handler;

use Throwable;
use RuntimeException;
use Elasticsearch\Client;
use Betterde\Logger\Logger;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Betterde\Logger\Formatter\ElasticsearchFormatter;
use Elasticsearch\Common\Exceptions\RuntimeException as ElasticsearchRuntimeException;

/**
 * Elasticsearch handler
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html
 *
 * Simple usage example:
 *
 *    $client = \Elasticsearch\ClientBuilder::create()
 *        ->setHosts($hosts)
 *        ->build();
 *
 *    $options = array(
 *        'index' => 'elastic_index_name',
 *        'type'  => 'elastic_doc_type',
 *    );
 *    $handler = new ElasticsearchHandler($client, $options);
 *    $log = new Logger('application');
 *    $log->pushHandler($handler);
 *
 * @author George
 */
class ElasticsearchHandler extends AbstractProcessingHandler
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array Handler config options
     */
    protected $options = [];

    /**
     * @param Client     $client  Elasticsearch Client object
     * @param array      $options Handler configuration
     * @param string|int $level   The minimum logging level at which this handler will be triggered
     * @param bool       $bubble  Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(Client $client, array $options = [], $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->client = $client;
        $this->options = array_merge(
            [
                'index'        => 'monolog', // Elastic index name
                'ignore_error' => false,     // Suppress Elasticsearch exceptions
            ],
            $options
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record): void
    {
        //for stack channels array from monolog
        if (!data_get($record['formatted'],'extra')) $record['formatted']['extra'] = config('logger.extra');
        
        $this->bulkSend([$record['formatted']]);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($formatter instanceof ElasticsearchFormatter) {
            return parent::setFormatter($formatter);
        }

        throw new InvalidArgumentException('ElasticsearchHandler is only compatible with ElasticsearchFormatter');
    }

    /**
     * Getter options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Date: 2019/11/27
     * @return FormatterInterface
     * @author George
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new ElasticsearchFormatter($this->options['index'], Arr::get($this->options, 'type', ''));
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records): void
    {
        $documents = $this->getFormatter()->formatBatch($records);
        $this->bulkSend($documents);
    }

    /**
     * Use Elasticsearch bulk API to send list of documents
     *
     * @param  array             $records
     * @throws RuntimeException
     */
    protected function bulkSend(array $records): void
    {
        try {
            $params = [
                'body' => [],
            ];

            foreach ($records as $record) {
                $index = [];
                $index['_index'] = $record['_index'];
                if (Arr::has($record, '_type') && strlen($record['_type']) > 0) {
                    $index['_type'] = $record['_type'];
                }
                $params['body'][] = [
                    'index' => $index
                ];
                unset($record['_index'], $record['_type']);

                $params['body'][] = $record;
            }

            $responses = $this->client->bulk($params);

            if ($responses['errors'] === true) {
                throw new ElasticsearchRuntimeException('Elasticsearch returned error for one of the records');
            }
        } catch (Throwable $e) {
            if (! $this->options['ignore_error']) {
                throw new RuntimeException('Error sending messages to Elasticsearch', 0, $e);
            }
        }
    }
}
