<?php declare(strict_types=1);

namespace Betterde\Logger\Handler;

/**
 * Base Handler class providing basic close() support as well as handleBatch
 *
 * Date: 2019/11/27
 * @author George
 * @package Betterde\Logger\Handler
 */
abstract class Handler implements HandlerInterface
{
    /**
     * Date: 2019/11/27
     * @param array $records
     * @author George
     */
    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    /**
     * Date: 2019/11/27
     * @author George
     */
    public function close(): void
    {
    }

    /**
     * @author George
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Throwable $e) {
            // do nothing
        }
    }

    /**
     * Date: 2019/11/27
     * @return array
     * @author George
     */
    public function __sleep()
    {
        $this->close();

        return array_keys(get_object_vars($this));
    }
}
