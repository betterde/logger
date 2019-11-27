<?php declare(strict_types=1);

namespace Betterde\Logger\Handler;

/**
 * Base Handler class providing the Handler structure, including processors and formatters
 *
 * Date: 2019/11/27
 * @author George
 * @package Betterde\Logger\Handler
 */
abstract class AbstractProcessingHandler extends AbstractHandler implements ProcessableHandlerInterface, FormattableHandlerInterface
{
    use ProcessableHandlerTrait;
    use FormattableHandlerTrait;

    /**
     * Date: 2019/11/27
     * @param array $record
     * @return bool
     * @author George
     */
    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        if ($this->processors) {
            $record = $this->processRecord($record);
        }

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Writes the record down to the log of the implementing handler
     * @param array $record
     * @author George
     */
    abstract protected function write(array $record): void;

    /**
     * Date: 2019/11/27
     * @author George
     */
    public function reset()
    {
        parent::reset();

        $this->resetProcessors();
    }
}
