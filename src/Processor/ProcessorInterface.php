<?php

namespace Betterde\Logger\Processor;

/**
 * An optional interface to allow labelling Monolog processors.
 *
 * Interface ProcessorInterface
 * @package Betterde\Logger\Processor
 * Date: 2019/11/28
 * @author George
 */
interface ProcessorInterface
{
    /**
     * @param array $records
     * @return array The processed records
     */
    public function __invoke(array $records);
}
