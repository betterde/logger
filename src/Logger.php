<?php

namespace Betterde\Logger;

use Betterde\Logger\Jobs\SendDocuments;
use Monolog\Logger as Monologger;

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
     * @throws \Exception
     * @author George
     */
    public function addRecord($level, $message, array $context = array()): bool
    {
        if (!$this->handlers) {
            $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
        }

        $levelName = static::getLevelName($level);

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling(array('level' => $level))) {
                $handlerKey = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        if (null === $handlerKey) {
            return false;
        }

        if (!static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        // php7.1+ always has microseconds enabled, so we do not need this hack
        if ($this->microsecondTimestamps && PHP_VERSION_ID < 70100) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime(null, static::$timezone);
        }
        $ts->setTimezone(static::$timezone);

        $record = array(
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => $ts,
            'extra' => array(),
        );

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

        } catch (Exception $e) {
            $this->handleException($e, $record);
        }

        return true;
    }
}
