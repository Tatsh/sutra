<?php
/**
 * Interface for classes that will respond to AJAX responses.
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
interface sAJAXResponder {
  /**
   * Handle an AJAX request.
   *
   * @param string $url Current path.
   * @param string $request_type Response type, POST or GET.
   * @return array Array of data.
   */
  public static function requestAt($url, $request_type);
}
