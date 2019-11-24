<?php

namespace Betterde\Logger;

/**
 * Date: 2019/11/24
 * @author George
 * @package Betterde\Logger
 */
class ElasticsearchLogger
{
    public function __invoke(array $config)
    {
        return app('betterde.logger');
    }
}
