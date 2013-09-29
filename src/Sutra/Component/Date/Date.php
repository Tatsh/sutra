<?php
namespace Sutra\Component\Date;

use Sutra\Component\Date\Exception\ProgrammerException;
use Sutra\Component\Date\Exception\ValidationException;

/**
 * Simple interface to date comparison, modification, and fuzzy difference
 *   string generation.
 *
 * @todo Localisation (probably in different class)
 *
 * @replaces fDate
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
     * @see #getFuzzyDifference
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
     * @replaces __construct Please note `'CURRENT_TIMESTAMP'` and
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
     * Returns date in `Y-m-d` format.
     *
     * @return string Date string.
     *
     * @replaces ::__toString
     */
    public function __toString()
    {
        return $this->date->format('Y-m-d');
    }

    /**
     * Adjusts to a relative time string such as '-1 week'.
     *
     * @param string $adjustment Relative time string.
     *
     * @return Date Returns a new instance of the object with the new adjusted
     *   date.
     *
     * @throws ValidationException If the adjustment string is not valid.
     *
     * @replaces ::adjust
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
     * Tests equality of 2 dates (by day only).
     *
     * @param mixed $otherDate Other date to compare. If not specified, current
     *   date will be used.
     *
     * @return boolean Returns if the dates are equal.
     *
     * @replaces ::eq
     */
    public function equals($otherDate = null)
    {
        $otherDate = new static($otherDate);

        return $this->date == $otherDate->date; // ??
    }

    /**
     * Returns date formatted according to format specified.
     *
     * @param string $format Format to use. Only date-related
     *   (and not time-related) letters are allowed.
     *
     * @return string Formatted date.
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
     * Gets a fuzzy difference string by comparing 2 dates.
     *
     * @param mixed   $otherDate Other date to compare. If not passed, today's
     *   date is used.
     * @param boolean $simple    If simple format should be used.
     *
     * @return string Fuzzy date string such as '1 day ago', '2 years from
     *   now'.
     *
     * @replaces ::getFuzzyDifference
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
     * Tests if this date is greater than other date (by day only).
     *
     * @param mixed $otherDate Other date to compare. If not specified, current
     *   date will be used.
     *
     * @return boolean Returns if this date is greater than other date.
     *
     * @replaces ::gt
     */
    public function greaterThan($otherDate = null)
    {
        $otherDate = new static($otherDate);
        return $this->date > $otherDate->date;
    }

    /**
     * Tests if this date is greater than or equal to other date (by day only).
     *
     * @param mixed $otherDate Other date to compare. If not specified, current
     *   date will be used.
     *
     * @return boolean Returns if this date is greater than or equal to other
     *   date.
     *
     * @replaces ::gte
     */
    public function greaterThanOrEqualTo($otherDate = null)
    {
        $otherDate = new static($otherDate);
        return $this->date >= $otherDate->date;
    }

    /**
     * Tests if this date is less than other date (by day only).
     *
     * @param mixed $otherDate Other date to compare. If not specified, current
     *   date will be used.
     *
     * @return boolean Returns if this date is less than other date.
     *
     * @replaces ::lt
     */
    public function lessThan($otherDate = null)
    {
        $otherDate = new static($otherDate);
        return $this->date < $otherDate->date;
    }

    /**
     * Tests if this date is less than or equal to other date (by day only).
     *
     * @param mixed $otherDate Other date to compare. If not specified, current
     *   date will be used.
     *
     * @return boolean Returns if this date is less than or equal to other
     *   date.
     *
     * @replaces ::lte
     */
    public function lessThanOrEqualTo($otherDate = null)
    {
        $otherDate = new static($otherDate);
        return $this->date <= $otherDate->date;
    }

    /**
     * Returns a new instance with modified date according to format.
     *
     * @param string $format Date format string.
     *
     * @return Date New instance with modified date.
     *
     * @replaces ::modify
     */
    public function modify($format)
    {
        return new static($this->format($format));
    }
}
