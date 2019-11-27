<?php declare(strict_types=1);

namespace Betterde\Logger\Processor;

/**
 * An optional interface to allow labelling Monolog processors.
 *
 * Interface ProcessorInterface
 * @package Betterde\Logger\Processor
 * Date: 2019/11/27
 * @author George
 */
interface ProcessorInterface
{
    /**
     * @param array $record
     * @return array The processed record
     */
    public function __invoke(array $record);
}
