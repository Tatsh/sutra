<?php
/**
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class sTimestamp extends fTimestamp {
  /**
   * Loose regular expression to match timestamp as defined by W3C for
   *   HTML 5 date/datetime fields. The main difference is allowing for
   *   a space between the date and the literal 'T'.
   *
   * @var string
   */
  const DATETIME_REGEX = '/^([1-2][0-9]{3})\-([0-1][0-9])\-([0-3][0-9])(?:\s+)?T([0-2][0-9])\:([0-5][0-9])\:([0-5][0-9](?:\.\d+)?)(?:Z|(?:[\-\+]([0-1][0-9])\:00))?$/';

  /**
   * Get a formatted timezone string such as +08:00 from 8 or -12:00 from -12.
   *
   * @param float $value The timezone, can be decimal.
   * @return string String, such as +08:00.
   */
  public static function formatTimezoneNumber($value) {
    $ret = '';
    $value = (float)$value;

    if ($value < 0) {
      if (abs($value) >= 10) {
        $ret = $value.':00';
      }
      else {
        $ret = '-0'.abs($value).':00';
      }
    }
    else {
      if ($value >= 10) {
        $ret = '+'.$value.':00';
      }
      else {
        $ret = '+0'.$value.':00';
      }
    }

    return $ret;
  }

  /**
   * Convert an RFC3339 (HTML 5 version) timestamp to UNIX. Timezone is
   *   ignored.
   *
   * HTML 5 mandates 2 extra constraints:
   * - the literal letters T and Z in the date/time syntax must always be uppercase
   * - the date-fullyear production is instead defined as four or more digits
   *     representing a number greater than 0
   *
   * @throws fValidationException If $throw is set to TRUE and the timestamp
   *   is invalid.
   *
   * @param string $rfc The RFC value, like: 1990-12-31T23:59:60Z or
   *   1996-12-19T16:39:57-08:00.
   * @param boolean $throw Throw an fValidationException if the timestamp is
   *   is invalid. Defaults to FALSE.
   * @return integer|boolean UNIX timestamp or boolean FALSE. If FALSE is
   *   returned, the string was invalid.
   */
  public static function RFC3339ToTimestamp($rfc, $throw = FALSE) {
    try {
      $matches = array();

      if (preg_match(self::DATETIME_REGEX, $rfc, $matches)) {
        $year = abs((int)$matches[1]);
        $month = abs((int)$matches[2]);
        $day = abs((int)$matches[3]);
        $hour = abs((int)$matches[4]);
        $minute = abs((int)$matches[5]);
        $second = abs($matches[6]);
        $datetime = $year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second;
        $timestamp = new self($datetime);
      }
      else {
        throw new fValidationException('The value specified could not be validated as a RFC3339 timestamp.');
      }

      return (int)$timestamp->format('U');
    }
    catch (fValidationException $e) {
      if ($throw) {
        throw $e;
      }
    }

    return FALSE;
  }
}
