<?php
namespace Sutra\Component\Date;

/**
 * Simple interface to date comparison, modification, and fuzzy difference
 *   string generation.
 *
 * @replaces fDate
 */
interface DateInterface
{
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
    public function adjust($adjustment);

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
    public function equals($otherDate = null);

    /**
     * Returns date formatted according to format specified.
     *
     * @param string $format Format to use. Only date-related
     *   (and not time-related) letters are allowed.
     *
     * @return string Formatted date.
     *
     * @replaces ::format
     */
    public function format($format);

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
    public function getFuzzyDifference($otherDate = null, $simple = false);

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
    public function greaterThan($otherDate = null);

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
    public function greaterThanOrEqualTo($otherDate = null);

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
    public function lessThan($otherDate = null);

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
    public function lessThanOrEqualTo($otherDate = null);

    /**
     * Returns a new instance with modified date according to format.
     *
     * @param string $format Date format string.
     *
     * @return Date New instance with modified date.
     *
     * @replaces ::modify
     */
    public function modify($format);

    /**
     * Returns date in `Y-m-d` format.
     *
     * @return string Date string.
     *
     * @replaces ::__toString
     */
    public function __toString();
}
