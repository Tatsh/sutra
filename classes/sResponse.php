<?php
/**
 * Common non-file related HTTP response helper class.
 *
 * @copyright Copyright (c) 2012 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sResponse {
  const sendNotModifiedHeader = 'sResponse::sendNotModifiedHeader';
  const sendForbiddenHeader   = 'sResponse::sendForbiddenHeader';

  /**
   * Send a 304 not modified header, if the content hasn't changed according to
   *   the headers sent in by the client.
   *
   * @param fTimestamp|integer $last_modified Time the file was last
   *   modified (fTimestamp object or UNIX timestamp).
   * @param string $etag Etag to use for this request.
   * @param integer $cache_time Time in seconds to cache for. Default is 2
   *   weeks.
   * @param boolean $accept_encoding Send Vary: Accept-Encoding header.
   *   Default is TRUE.
   * @return void
   */
  public static function sendNotModifiedHeader($last_modified, $etag, $cache_time = 1209600, $accept_encoding = TRUE) {
    $cache_time = (int)$cache_time;

    if ($last_modified instanceof fTimestamp) {
      $last_modified = $last_modified->format('U');
    }
    else {
      $last_modified = (int)$last_modified;
    }
    $last_modified = gmdate('D, d M Y H:i:s', $last_modified).' GMT';

    if ($accept_encoding) {
      header('Vary: Accept-Encoding');
    }

    header('Cache-Control: max-age='.$cache_time);
    header('Last-Modified: '.$last_modified);
    header('Etag: '.$etag);

    $modified = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified;
    $none = isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag;
    if ($modified || $none) {
      header('HTTP/1.1 304 Not Modified');
    }
  }

  // @codeCoverageIgnoreStart
  /**
   * Sends a 403 restricted content header.
   *
   * @return void
   */
  public static function sendForbiddenHeader() {
    header('HTTP/1.1 403 Forbidden');
  }

  /**
   * Forces use as a static class.
   *
   * @return sResponse
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
