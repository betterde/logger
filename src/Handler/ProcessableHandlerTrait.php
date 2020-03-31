<?php declare(strict_types=1);

namespace Betterde\Logger\Handler;

use LogicException;
use Monolog\ResettableInterface;
use Monolog\Handler\HandlerInterface;

/**
 * Helper trait for implementing ProcessableInterface
 *
 * Trait ProcessableHandlerTrait
 * @package Monolog\Handler
 * Date: 2019/11/27
 * @author George
 */
trait ProcessableHandlerTrait
{
    /**
     * @var array
     * Date: 2019/11/27
     * @author George
     */
    protected $processors = [];

    /**
     * Date: 2019/11/27
     * @param callable $callback
     * @return HandlerInterface
     * @author George
     */
    public function pushProcessor(callable $callback): HandlerInterface
    {
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * Date: 2019/11/27
     * @return callable
     * @author George
     */
    public function popProcessor(): callable
    {
        if (!$this->processors) {
            throw new LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * Date: 2019/11/27
     * @param array $record
     * @return array
     * @author George
     */
    protected function processRecord(array $record): array
    {
        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }

        return $record;
    }

    /**
     * Date: 2019/11/27
     * @author George
     */
    protected function resetProcessors(): void
    {
        foreach ($this->processors as $processor) {
            if ($processor instanceof ResettableInterface) {
                $processor->reset();
            }
        }
    }
}
