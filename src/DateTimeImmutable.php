<?php declare(strict_types=1);

namespace Betterde\Logger;

use DateTimeZone;

/**
 * Overrides default json encoding of date time objects
 *
 * Date: 2019/11/27
 * @author George
 * @package Betterde\Logger
 */
class DateTimeImmutable extends \DateTimeImmutable implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $useMicroseconds;

    /**
     * DateTimeImmutable constructor.
     * @param bool $useMicroseconds
     * @param DateTimeZone|null $timezone
     * @throws \Exception
     */
    public function __construct(bool $useMicroseconds, ?DateTimeZone $timezone = null)
    {
        $this->useMicroseconds = $useMicroseconds;

        parent::__construct('now', $timezone);
    }

    /**
     * Date: 2019/11/27
     * @return string
     * @author George
     */
    public function jsonSerialize(): string
    {
        if ($this->useMicroseconds) {
            return $this->format('Y-m-d\TH:i:s.uP');
        }

        return $this->format('Y-m-d\TH:i:sP');
    }

    /**
     * Date: 2019/11/27
     * @return string
     * @author George
     */
    public function __toString(): string
    {
        return $this->jsonSerialize();
    }
}
