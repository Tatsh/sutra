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

    header('Cache-Control: max-age='.$cache_time); // 2 weeks
    header('Last-Modified: '.$last_modified);
    header('Etag: '.$etag);

    $modified = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified;
    $none = isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag;
    if ($modified || $none) {
      header('HTTP/1.1 304 Not Modified');
    }
  }
}
