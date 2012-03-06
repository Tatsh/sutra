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
  const DATETIME_REGEX = '/^([1-2][0-9]{3})\-([0-1][0-9])\-([0-3][0-9])(\s+)?T([0-2][0-9])\:([0-5][0-9])\:([0-5][0-9])(Z|([\-\+]([0-1][0-9])\:00))?$/';

  /**
   * List of timezones with UTF offset as key.
   *
   * @var array
   */
  private static $timezone_list = array(
    -12  => 'Pacific/Kwajalein',
    -11  => 'Pacific/Midway',
    -10  => 'Pacific/Honolulu',
    -9   => 'America/Anchorage',
    -8   => 'America/Los_Angeles',
    -7   => 'America/Denver',
    -6   => 'America/Chicago',
    -5   => 'America/New_York',
    -4   => 'America/Caracas',
    -3.5 => 'America/St_Johns',
    -2   => 'America/Sao_Paulo',
    -1   => 'Atlantic/Cape_Verde',
    0    => 'Europe/London',
    1    => 'Europe/Brussels',
    2    => 'Europe/Kaliningrad',
    3    => 'Asia/Baghdad',
    3.5  => 'Asia/Tehran',
    4    => 'Asia/Muscat',
    4.5  => 'Asia/Kabul',
    5    => 'Asia/Karachi',
    5.5  => 'Asia/Calcutta',
    5.75 => 'Asia/Kathmandu',
    6    => 'Asia/Almaty',
    7    => 'Asia/Bangkok',
    8    => 'Asia/Hong_Kong',
    9    => 'Asia/Tokyo',
    9.5  => 'Australia/Adelaide',
    10   => 'Pacific/Guam',
    11   => 'Asia/Magadan',
    12   => 'Pacific/Auckland',
  );

  /**
   * Get a timezone via UTC offset, such as -6, -7, 3.5.
   *
   * @throws fProgrammerException If the timezone offset does not exist.
   *
   * @param float $offset Offset to use.
   * @return string Standard timezone string.
   */
  public static function timezoneStringWithOffset($offset) {
    $tz = self::$timezone_list;

    if (!isset($tz[$offset])) {
      throw new fProgrammerException('Offset %d does not exist.', $offset);
    }

    return $tz[$offset];
  }

  /**
   * Get a formatted timezone string such as +08:00 from 8 or -12:00 from -12.
   *
   * @param float $value The timezone, can be decimal.
   * @return string String, such as +8:00.
   */
  public static function formatTimezoneWithNumber($value) {
    $ret = '';

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
   * Get the full time zone list as UTC offset => UNIX Timezone name (string).
   *
   * @return array Array of time zones.
   */
  public static function getTimezones() {
    return self::$timezone_list;
  }

  /**
   * Convert an RFC3339 (HTML 5 version) timestamp to UNIX.
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
   * @return int UNIX timestamp. If 0 is returned, the timestamp was invalid.
   */
  public static function rfc3339ToUNIX($rfc, $throw = FALSE) {
    $matches = array();
    try {
      if (preg_match(self::DATETIME_REGEX, $rfc, $matches)) {
        $year = abs((int)$matches[1]);
        $month = abs((int)$matches[2]);
        $day = abs((int)$matches[3]);
        $hour = abs((int)$matches[5]);
        $minute = abs((int)$matches[6]);
        $second = abs((int)$matches[7]);

        if ($hour > 23) {
          throw new fValidationException('Invalid hour value.');
        }
        if ($day > 31) {
          throw new fValidationException('Invalid day value.');
        }
        if ($month > 12) {
          throw new fValidationException('Invalid month value.');
        }

        if (isset($matches[8]) && $matches[8] !== 'Z') {
          // Should be the timezone with leading 0, can be positive or negative
          $tz = (int)$matches[8];

          // Ignore invalid time zone numbers
          if ($tz < -12 || $tz > 12) {
            throw new fValidationException('Invalid timezone value.');
          }

          $tz = self::timezoneStringWithOffset($tz);
          $timestamp = new self($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second, $tz);
        }
        else {
          $timestamp = new self($year.'-'.$month.'-'.$day.' '.$hour.':'.$minute.':'.$second);
        }
      }
      else {
        throw new fValidationException('The value specified could not be validated as a RFC3339 timestamp.');
      }
    }
    catch (fValidationException $e) {
      if ($throw) {
        throw $e;
      }
      return 0;
    }

    return (int)$timestamp->format('U');
  }
}
