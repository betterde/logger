<?php

namespace Betterde\Logger;

use Exception;
use Monolog\DateTimeImmutable;
use Monolog\Logger as Monologger;
use Betterde\Logger\Jobs\SendDocuments;

/**
 * Date: 2019/11/24
 * @author George
 * @package Betterde\Logger
 */
class Logger extends Monologger
{
    /**
     * @var array $records
     * Date: 2019/11/24
     * @author George
     */
    public $records = [];

    /**
     * Date: 2019/11/24
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     * @throws \Throwable
     * @author George
     */
    public function addRecord($level, $message, array $context = array()): bool
    {
        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        foreach ($this->handlers as $key => $handler) {
            if ($handler->isHandling(['level' => $level])) {
                $handlerKey = $key;
                break;
            }
        }

        if (null === $handlerKey) {
            return false;
        }

        if (null === $handlerKey) {
            return false;
        }

        $levelName = static::getLevelName($level);

        $record = [
            'message' => $message,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => new DateTimeImmutable($this->microsecondTimestamps, $this->timezone),
            'extra' => [],
        ];

        if (isset($context['exception']) && $context['exception'] instanceof Exception) {
            /**
             * @var Exception $exception
             */
            $exception = $context['exception'];
            $record['context'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'previous' => $exception->getPrevious(),
                'trace' => config('logger.exception.trace') ? $exception->getTraceAsString() : []
            ];
        } else {
            $record['context'] = $context;
        }

        try {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }

            if (config('logger.batch')) {
                $this->records[] = $record;
            } else {
                if (config('logger.queue')) {
                    dispatch(new SendDocuments($record));
                } else {
                    // advance the array pointer to the first handler that will handle this record
                    reset($this->handlers);
                    while ($handlerKey !== key($this->handlers)) {
                        next($this->handlers);
                    }

                    while ($handler = current($this->handlers)) {
                        if (true === $handler->handle($record)) {
                            break;
                        }

                        next($this->handlers);
                    }
                }
            }
        } catch (Exception $exception) {
            $this->handleException($exception, $record);
        }

        return true;
    }
}
