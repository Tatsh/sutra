<?php
namespace Sutra\Component\Date;

use Sutra\Component\Date\Exception\ProgrammerException;
use Sutra\Component\Date\Exception\ValidationException;

/**
 * {@inheritdoc}
 *
 * @todo Localisation (probably in different class).
 */
class Date implements DateInterface
{
    /**
     * PHP `\DateTime` instance.
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * Break points for fuzzy differences.
     *
     * @var array
     *
     * @see #getFuzzyDifference()
     */
    private static $breakPoints = array(
        // 5 days
        432000     => array(86400,    'day',   'days'),
        // 3 weeks
        1814400    => array(604800,   'week',  'weeks'),
        // 9 months
        23328000   => array(2592000,  'month', 'months'),
        // largest int
        2147483647 => array(31536000, 'year',  'years'),
    );

    /**
     * Constructor.
     *
     * @param mixed $date Date. Supports objects with a callable
     *   `__toString()` method, PHP \DateTime, and UNIX timestamp.
     *
     * @replaces ::__construct Please note `'CURRENT_TIMESTAMP'` and
     *   `'CURRENT_DATE'` strings are no longer supported.
     */
    public function __construct($date = null)
    {
        $timestamp = time();

        if (is_numeric($date) && preg_match('#^-?\d+$#D', $date)) {
            $timestamp = (int) $date;
        }
        else if (is_object($date)) {
            if ($date instanceof static) {
                $timestamp = (int) $date->date->format('U');
            }
            else if ($date instanceof \DateTime) {
                $timestamp = (int) $date->format('U');
            }
            else if (is_callable(array($date, '__toString'))) {
                $timestamp = strtotime((string) $date);
            }
        }
        else if ($date) {
            $timestamp = strtotime($date);
        }

        if ($timestamp === false || $timestamp < 0) {
            throw new ValidationException('The date given "%s" was not able to be parsed', $date);
        }

        $this->date = new \DateTime(date('Y-m-d 00:00:00', $timestamp));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->date->format('Y-m-d');
    }

    /**
     * {@inheritdoc}
     */
    public function adjust($adjustment)
    {
        $timestamp = strtotime($adjustment, $this->date->format('U'));

        if ($timestamp === false || $timestamp < 0) {
            throw new ValidationException('The adjustment specified, "%s", does not appear to be a valid relative date measurement', $adjustment);
        }

        return new static($timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function equals($otherDate = null)
    {
        $otherDate = new static($otherDate);

        return $this->date == $otherDate->date; // ??
    }

    /**
     * {@inheritdoc}
     */
    public function format($format)
    {
        $restrictedFormats = 'aABcegGhHiIOPrsTuUZ';

        if (preg_match('#(?!\\\\).[' . $restrictedFormats . ']#', $format)) {
            throw new ProgrammerException('The formatting string, "%s", contains one of the following non-date formatting characters: %s', $format, join(', ', str_split($restrictedFormats)));
        }

        return $this->date->format($format);
    }

    /**
     * {@inheritdoc}
     */
    public function getFuzzyDifference($otherDate = null, $simple = false)
    {
        // Allow alternate signature
        if (is_bool($otherDate)) {
            $simple = $otherDate;
            $otherDate = null;
        }

        $relativeToNow = $otherDate === null;
        $otherDate = new static($otherDate);
        $diff = $this->date->format('U') - $otherDate->date->format('U');

        if (abs($diff) < 86400) {
            if ($relativeToNow) {
                return 'today';
            }

            return 'same day';
        }

        foreach (static::$breakPoints as $breakPoint => $unitInfo) {
            if (abs($diff) > $breakPoint) {
                continue;
            }

            $unitDiff = round(abs($diff / $unitInfo[0]));
            $units = ($unitDiff == 0 || $unitDiff > 1) ? $unitInfo[2] : $unitInfo[1];

            break;
        }

        if ($simple) {
            return sprintf('%s %s', $unitDiff, $units);
        }

        if ($relativeToNow) {
            if ($diff > 0) {
                return sprintf('%s %s from now', $unitDiff, $units);
            }

            return sprintf('%s %s ago', $unitDiff, $units);
        }

        if ($diff > 0) {
            return sprintf('%s %s after', $unitDiff, $units);
        }

        return sprintf('%s %s before', $unitDiff, $units);
    }

    /**
     * {@inheritdoc}
     */
    public function greaterThan($otherDate = null)
    {
        $otherDate = new static($otherDate);

        return $this->date > $otherDate->date;
    }

    /**
     * {@inheritdoc}
     */
    public function greaterThanOrEqualTo($otherDate = null)
    {
        $otherDate = new static($otherDate);

        return $this->date >= $otherDate->date;
    }

    /**
     * {@inheritdoc}
     */
    public function lessThan($otherDate = null)
    {
        $otherDate = new static($otherDate);

        return $this->date < $otherDate->date;
    }

    /**
     * {@inheritdoc}
     */
    public function lessThanOrEqualTo($otherDate = null)
    {
        $otherDate = new static($otherDate);

        return $this->date <= $otherDate->date;
    }

    /**
     * {@inheritdoc}
     */
    public function modify($format)
    {
        return new static($this->format($format));
    }
}
