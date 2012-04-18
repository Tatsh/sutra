<?php
/**
 * Array utility functions.
 *
 * @copyright Copyright (c) 2012 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sArray {
  const hasRequiredKeys = 'sArray::hasRequiredKeys';

  /**
   * Checks if an array has all required keys.
   *
   * @param array $array Array to check.
   * @param array $required_keys Keys that must be in the array.
   * @param boolean $only_required If the array should only have the required keys.
   * @return string|boolean Returns boolean TRUE if the array is valid, or the
   *   missing key.
   *
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public static function hasRequiredKeys(array $array, array $required_keys, $only_required = FALSE) {
    if (empty($array)) {
      return array_shift($required_keys);
    }

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

  // @codeCoverageIgnoreStart
  /**
   * Forces use as a static class.
   *
   * @return sArray
   */
  private function __construct() {}
  // @codeCoverageIgnoreEnd
}

/**
 * Copyright (c) 2012 Andrew Udvare <andrew@bne1.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
