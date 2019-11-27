<?php declare(strict_types=1);

namespace Betterde\Logger\Formatter;

use SoapFault;
use Throwable;
use RuntimeException;
use JsonSerializable;
use DateTimeInterface;
use Betterde\Logger\Utils;
use Betterde\Logger\DateTimeImmutable;

/**
 * Normalizes incoming records to remove objects/resources so it's easier to dump to various targets
 *
 * Date: 2019/11/27
 * @author George
 * @package Betterde\Logger\Formatter
 */
class NormalizerFormatter implements FormatterInterface
{
    public const SIMPLE_DATE = "Y-m-d\TH:i:sP";

    /**
     * @var string|null
     * Date: 2019/11/27
     * @author George
     */
    protected $dateFormat;
    /**
     * @var int
     * Date: 2019/11/27
     * @author George
     */
    protected $maxNormalizeDepth = 9;
    /**
     * @var int
     * Date: 2019/11/27
     * @author George
     */
    protected $maxNormalizeItemCount = 1000;

    /**
     * @var int $jsonEncodeOptions
     * Date: 2019/11/27
     * @author George
     */
    private $jsonEncodeOptions = Utils::DEFAULT_JSON_FLAGS;

    /**
     * @param string|null $dateFormat The format of the timestamp: one supported by DateTime::format
     */
    public function __construct(?string $dateFormat = null)
    {
        $this->dateFormat = null === $dateFormat ? static::SIMPLE_DATE : $dateFormat;
        if (!function_exists('json_encode')) {
            throw new RuntimeException('PHP\'s json extension is required to use Monolog\'s NormalizerFormatter');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        return $this->normalize($record);
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * The maximum number of normalization levels to go through
     */
    public function getMaxNormalizeDepth(): int
    {
        return $this->maxNormalizeDepth;
    }

    /**
     * Date: 2019/11/27
     * @param int $maxNormalizeDepth
     * @return $this
     * @author George
     */
    public function setMaxNormalizeDepth(int $maxNormalizeDepth): self
    {
        $this->maxNormalizeDepth = $maxNormalizeDepth;

        return $this;
    }

    /**
     * The maximum number of items to normalize per level
     */
    public function getMaxNormalizeItemCount(): int
    {
        return $this->maxNormalizeItemCount;
    }

    /**
     * Date: 2019/11/27
     * @param int $maxNormalizeItemCount
     * @return $this
     * @author George
     */
    public function setMaxNormalizeItemCount(int $maxNormalizeItemCount): self
    {
        $this->maxNormalizeItemCount = $maxNormalizeItemCount;

        return $this;
    }

    /**
     * Enables `json_encode` pretty print.
     */
    public function setJsonPrettyPrint(bool $enable): self
    {
        if ($enable) {
            $this->jsonEncodeOptions |= JSON_PRETTY_PRINT;
        } else {
            $this->jsonEncodeOptions ^= JSON_PRETTY_PRINT;
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @param int $depth
     * @return int|bool|string|null|array
     */
    protected function normalize($data, int $depth = 0)
    {
        if ($depth > $this->maxNormalizeDepth) {
            return 'Over ' . $this->maxNormalizeDepth . ' levels deep, aborting normalization';
        }

        if (null === $data || is_scalar($data)) {
            if (is_float($data)) {
                if (is_infinite($data)) {
                    return ($data > 0 ? '' : '-') . 'INF';
                }
                if (is_nan($data)) {
                    return 'NaN';
                }
            }

            return $data;
        }

        if (is_array($data)) {
            $normalized = [];

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ > $this->maxNormalizeItemCount) {
                    $normalized['...'] = 'Over ' . $this->maxNormalizeItemCount . ' items ('.count($data).' total), aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth + 1);
            }

            return $normalized;
        }

        if ($data instanceof DateTimeInterface) {
            return $this->formatDate($data);
        }

        if (is_object($data)) {
            if ($data instanceof Throwable) {
                return $this->normalizeException($data, $depth);
            }

            if ($data instanceof JsonSerializable) {
                $value = $data->jsonSerialize();
            } elseif (method_exists($data, '__toString')) {
                $value = $data->__toString();
            } else {
                // the rest is normalized by json encoding and decoding it
                $encoded = $this->toJson($data, true);
                if ($encoded === false) {
                    $value = 'JSON_ERROR';
                } else {
                    $value = json_decode($encoded, true);
                }
            }

            return [Utils::getClass($data) => $value];
        }

        if (is_resource($data)) {
            return sprintf('[resource(%s)]', get_resource_type($data));
        }

        return '[unknown('.gettype($data).')]';
    }

    /**
     * @param Throwable $e
     * @param int $depth
     * @return array
     */
    protected function normalizeException(Throwable $e, int $depth = 0)
    {
        if ($e instanceof JsonSerializable) {
            return (array) $e->jsonSerialize();
        }

        $data = [
            'class' => Utils::getClass($e),
            'message' => $e->getMessage(),
            'code' => (int) $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
        ];

        if ($e instanceof SoapFault) {
            if (isset($e->faultcode)) {
                $data['faultcode'] = $e->faultcode;
            }

            if (isset($e->faultactor)) {
                $data['faultactor'] = $e->faultactor;
            }

            if (isset($e->detail) && (is_string($e->detail) || is_object($e->detail) || is_array($e->detail))) {
                $data['detail'] = is_string($e->detail) ? $e->detail : reset($e->detail);
            }
        }

        $trace = $e->getTrace();
        foreach ($trace as $frame) {
            if (isset($frame['file'])) {
                $data['trace'][] = $frame['file'].':'.$frame['line'];
            }
        }

        if ($previous = $e->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous, $depth + 1);
        }

        return $data;
    }

    /**
     * Return the JSON representation of a value
     *
     * @param mixed $data
     * @param bool $ignoreErrors
     * @return string if encoding fails and ignoreErrors is true 'null' is returned
     */
    protected function toJson($data, bool $ignoreErrors = false): string
    {
        return Utils::jsonEncode($data, $this->jsonEncodeOptions, $ignoreErrors);
    }

    /**
     * Date: 2019/11/27
     * @param DateTimeInterface $date
     * @return string
     * @author George
     */
    protected function formatDate(DateTimeInterface $date)
    {
        // in case the date format isn't custom then we defer to the custom DateTimeImmutable
        // formatting logic, which will pick the right format based on whether useMicroseconds is on
        if ($this->dateFormat === self::SIMPLE_DATE && $date instanceof DateTimeImmutable) {
            return (string) $date;
        }

        return $date->format($this->dateFormat);
    }

    /**
     * Date: 2019/11/27
     * @param $option
     * @author George
     */
    protected function addJsonEncodeOption($option)
    {
        $this->jsonEncodeOptions |= $option;
    }

    /**
     * Date: 2019/11/27
     * @param $option
     * @author George
     */
    protected function removeJsonEncodeOption($option)
    {
        $this->jsonEncodeOptions ^= $option;
    }
}
