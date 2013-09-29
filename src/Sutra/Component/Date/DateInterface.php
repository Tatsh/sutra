<?php
namespace Sutra\Component\Date;

/**
 * @replaces fDate
 */
interface DateInterface
{
    /**
     * @replaces ::adjust
     */
    public function adjust($adjustment);

    /**
     * @replaces ::eq
     */
    public function equals($otherDate = null);

    /**
     * @replaces ::format
     */
    public function format($format);

    /**
     * @replaces ::getFuzzyDifference
     */
    public function getFuzzyDifference($otherDate = null, $simple = false);

    /**
     * @replaces ::gt
     */
    public function greaterThan($otherDate = null);

    /**
     * @replaces ::gte
     */
    public function greaterThanOrEqualTo($otherDate = null);

    /**
     * @replaces ::lt
     */
    public function lessThan($otherDate = null);

    /**
     * @replaces ::lte
     */
    public function lessThanOrEqualTo($otherDate = null);

    /**
     * @replaces ::modify
     */
    public function modify($format);

    /**
     * @repalces ::__toString
     */
    public function __toString();
}
