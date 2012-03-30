<?php
/**
 * Array utility functions.
 *
 * @copyright Copyright (c) 2011 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.0
 */
class sArray {
  /**
   * Checks if an array has all required keys.
   *
   * @param array $array Array to check.
   * @param array $required_keys Keys that must be in the array.
   * @param boolean $only_required If the array should only have the required keys.
   * @return string|boolean Returns boolean TRUE if the array is valid, or the
   *   missing key.
   *
   * @SuppressWarnings("unused")
   */
  public static function hasRequiredKeys(array $array, array $required_keys, $only_required = FALSE) {
    foreach ($required_keys as $key) {
      if (!array_key_exists($key, $array)) {
        return $key;
      }
    }

    if ($only_required) {
      foreach ($array as $key => $value) {
        if (!in_array($key, $required_keys)) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }
}
