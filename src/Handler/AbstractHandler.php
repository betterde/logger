<?php declare(strict_types=1);

namespace Betterde\Logger\Handler;

use Betterde\Logger\Logger;
use Betterde\Logger\ResettableInterface;

/**
 * Base Handler class providing basic level/bubble support
 *
 * Date: 2019/11/27
 * @author George
 * @package Betterde\Logger\Handler
 */
abstract class AbstractHandler extends Handler implements ResettableInterface
{
    /**
     * @var int
     * Date: 2019/11/27
     * @author George
     */
    protected $level = Logger::DEBUG;

    /**
     * @var bool
     * Date: 2019/11/27
     * @author George
     */
    protected $bubble = true;

    /**
     * @param int|string $level  The minimum logging level at which this handler will be triggered
     * @param bool       $bubble Whether the messages that are handled can bubble up the stack or not
     * @author George
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true)
    {
        $this->setLevel($level);
        $this->bubble = $bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record): bool
    {
        return $record['level'] >= $this->level;
    }

    /**
     * Sets minimum logging level at which this handler will be triggered.
     *
     * @param  int|string $level Level or level name
     * @return self
     */
    public function setLevel($level): self
    {
        $this->level = Logger::toMonologLevel($level);

        return $this;
    }

    /**
     * Gets minimum logging level at which this handler will be triggered.
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Sets the bubbling behavior.
     *
     * @param  bool $bubble true means that this handler allows bubbling.
     *                      false means that bubbling is not permitted.
     * @return self
     */
    public function setBubble(bool $bubble): self
    {
        $this->bubble = $bubble;

        return $this;
    }

    /**
     * Gets the bubbling behavior.
     *
     * @return bool true means that this handler allows bubbling.
     *              false means that bubbling is not permitted.
     */
    public function getBubble(): bool
    {
        return $this->bubble;
    }

    /**
     * Date: 2019/11/27
     * @author George
     */
    public function reset()
    {
    }
}
