<?php
/**
 * Manages cached CSS output.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraRouters
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class CSSActionController extends MoorActionController {
  /**
   * Prints cached CSS or returns a 304 response. Exits here.
   *
   * @return void
   */
  public function css() {
    $cache = sCache::getInstance();
    $key = 'sTemplate::'.getcwd().'::last_combined_css';
    $cached = $cache->get($key);

    $media = substr(fRequest::get('media', 'string'), 0, -4);
    $media = base64_decode($media);

    header('Content-type: text/css');

    if ($media && is_array($cached)) {
      // http://code.google.com/speed/page-speed/docs/caching.html#LeverageProxyCaching
      header('Vary: Accept-Encoding');
      header('Cache-Control: max-age=1209600'); // 2 weeks
      header('Pragma: ');
      $last_modified = gmdate('D, d M Y H:i:s', filemtime($_SERVER['SCRIPT_FILENAME'])).' GMT';
      header('Last-Modified: '.$last_modified);
      $etag = md5_file($_SERVER['SCRIPT_FILENAME']);
      header('Etag: '.$etag);

      $modified = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified;
      $none = isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag;
      if ($modified || $none) {
        header('HTTP/1.1 304 Not Modified');
        exit;
      }

      $css_text = isset($cached[$media]) ? $cached[$media] : '';
      print $css_text;
    }

    exit;
  }
}
