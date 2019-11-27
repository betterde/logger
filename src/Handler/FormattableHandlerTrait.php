<?php declare(strict_types=1);

namespace Monolog\Handler;

use Betterde\Logger\Formatter\FormatterInterface;
use Betterde\Logger\Formatter\LineFormatter;

/**
 * Helper trait for implementing FormattableInterface
 *
 * Trait FormattableHandlerTrait
 * @package Monolog\Handler
 * Date: 2019/11/27
 * @author George
 */
trait FormattableHandlerTrait
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * Date: 2019/11/27
     * @param FormatterInterface $formatter
     * @return HandlerInterface
     * @author George
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Date: 2019/11/27
     * @return FormatterInterface
     * @author George
     */
    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            $this->formatter = $this->getDefaultFormatter();
        }

        return $this->formatter;
    }

    /**
     * Gets the default formatter.
     *
     * Overwrite this if the LineFormatter is not a good default for your handler.
     * @return FormatterInterface
     * @author George
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter();
    }
}
