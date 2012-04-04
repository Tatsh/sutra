<?php
/**
 * POST request handling for a particular URL. Any classes that implement this
 *   interface must be included and cannot reliably be auto-loaded.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.example.com/
 *
 * @version 1.0
 */
interface sPostRequestProcessor {
  /**
   * Handles a submission. Values should be retrieved as normal with the
   *   fRequest class. Throw an fValidationException if any values are
   *   invalid.
   *
   * @throws fValidationException If any values are invalid.
   *
   * @return void
   */
  public static function submit();

  /**
   * Get the URLs the class handles.
   *
   * @return array Array of string URLs.
   */
  public static function getURLs();
}
