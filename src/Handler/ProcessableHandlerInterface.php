<?php declare(strict_types=1);

namespace Betterde\Logger\Handler;

use Betterde\Logger\Processor\ProcessorInterface;

/**
 * Interface to describe loggers that have processors
 *
 * Interface ProcessableHandlerInterface
 * @package Betterde\Logger\Handler
 * Date: 2019/11/27
 * @author George
 */
interface ProcessableHandlerInterface
{
    /**
     * Adds a processor in the stack.
     *
     * @param  ProcessorInterface|callable $callback
     * @return HandlerInterface            self
     */
    public function pushProcessor(callable $callback): HandlerInterface;

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @throws \LogicException In case the processor stack is empty
     * @return callable
     */
    public function popProcessor(): callable;
}
