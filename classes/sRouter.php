<?php
/**
 * sRouter version that uses Moor.
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
class sRouter {
  /**
   * The date format for the Last-Modified header, for use with gmdate().
   *
   * @var string
   * @see gmdate()
   */
  const LAST_MODIFIED_DATE_FORMAT = 'D, d M Y H:i:s';

  /**
   * Set of routes for Moor.
   *
   * @var array
   */
  protected static $routes = array();

  /**
   * Cached router aliases.
   *
   * @var array
   */
  protected static $aliases = array();

  /**
   * The current working directory.
   *
   * @var string
   */
  private static $cwd = '';

  /**
   * Paths to search for that have .route files.
   *
   * @var array
   */
  private static $route_files_paths = array();

  /**
   * Add a path that has .route files.
   *
   * @param string $path Path (relative to site root or complete), without
   *   ending slash.
   * @return void
   */
  public static function addRoutesFilePath($path) {
    self::$route_files_paths[] = $path;
  }

  /**
   * Reset all route files paths.
   *
   * @return void
   */
  public static function resetRouteFilesPaths() {
    self::$route_files_paths = array();
  }

  /**
   * Get the routes. Reads from paths that are in the $route_files_paths array.
   *
   * If not in production mode, the routes will be reloaded on every page load.
   *
   * @return array Array of paths mapped to callbacks.
   */
  public static function getRoutes() {
    if (!self::$cwd) {
      self::$cwd = getcwd();
    }

    $config = sConfiguration::getInstance();
    $cache = sCache::getInstance();
    $production_mode = $config->getProductionModeOn();
    $routes_key = __CLASS__.'::'.self::$cwd.'::moor_routes';
    $router_aliases_key = __CLASS__.'::'.self::$cwd.'::router_aliases';

    if (!$production_mode || fURL::get() == '/admin/clear-cache') {
      $cache->set($routes_key, array());
      $cache->set($router_aliases_key, array());
    }

    if (empty(self::$aliases)) {
      self::$aliases = $cache->get($router_aliases_key, array());

      if (empty(self::$aliases)) {
        $alias_records = fRecordSet::build('RouterAlias');

        foreach ($alias_records as $record) {
          self::$aliases[$record->getAlias()] = $record->getPath();
        }

        $cache->set($router_aliases_key, self::$aliases);
      }
    }

    if (empty(self::$routes)) {
      self::$routes = $cache->get($routes_key, array());

      if (empty(self::$routes)) {
        $files = array();
        foreach (self::$route_files_paths as $path) {
          $files = array_merge($files, glob($path.'/*.route'));
        }

        if (empty($files)) {
          fCore::debug('No routes found!');
          if (!$production_mode) {
            print 'Error';
          }
          exit;
        }

        foreach ($files as $file) {
          $file = new fFile($file);
          foreach ($file as $line) {
            $line = trim($line);

            $matches = array();
            preg_match('#^(/.*)\s+\=\s+([A-Za-z\:]+)#', $line, $matches);

            if (empty($matches) || !isset($matches[1]) || !isset($matches[2])) {
              continue;
            }

            $path = $matches[1];
            $method = $matches[2];

            $path = trim($path);
            $method = trim($method);

            self::$routes[$path] = $method;
          }
        }

        $cache->set($routes_key, self::$routes);
      }

      foreach (self::$routes as $path => $method) {
        Moor::route($path, $method);
      }
    }

    return self::$routes;
  }

  /**
   * Called from sCore::main() if the request method is GET.
   *   Handles a standard GET request with Moor.
   *
   * @return void
   */
  public static function handle() {
    self::getRoutes();
    $request_path = fURL::get();

    if ($request_path !== '/' && substr($request_path, -1) === '/') {
      header('Cache-Control: max-age=1209600'); // 2 weeks
      header('HTTP/1.1 301 Moved Permanently');
      fURL::redirect(substr($request_path, 0, -1));
      return;
    }

    if (isset(self::$aliases[$request_path])) {
      $_SERVER['REQUEST_URI'] = self::$aliases[$request_path];
      $query = trim(fURL::getQueryString());
      if ($query) {
        $_SERVER['REQUEST_URI'] .= '?'.$query;
      }
    }

    if (isset(self::$routes['/404'])) {
      Moor::setNotFoundCallback(self::$routes['/404']);
    }

    Moor::run();
  }

  /**
   * Get a link, possibly a path alias.
   *
   * @param string $path Non-aliased path to link to.
   * @param string $content Content with the a tag.
   * @param array Array of attributes for the a tag.
   * @return string HTML tag.
   */
  public static function linkTo($path, $content, array $attr = array()) {
    self::getRoutes();
    unset($attr['href']);

    $key = __CLASS__.'::'.self::$cwd.'::alias::'.$path;
    $path = trim($path);
    $cache = sCache::getInstance();
    $cached_path = $cache->get($key);

    if (is_null($cached_path)) {
      foreach (self::$aliases as $alias => $stored_path) {
        if ($path === $stored_path) {
          $cached_path = $alias;
          $path = $cached_path;
          break;
        }
      }
      $cache->set($key, $cached_path);
    }
    else {
      $path = $cached_path;
    }

    $attr = array_merge($attr, array(
      'href' => $path,
    ));

    return sHTML::tag('a', $attr, $content);
  }

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

    header('Vary: Accept-Encoding');
    header('Cache-Control: max-age='.$cache_time); // 2 weeks
    header('Last-Modified: '.$last_modified);
    header('Etag: '.$etag);

    $modified = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified;
    $none = isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag;
    if ($modified || $none) {
      header('HTTP/1.1 304 Not Modified');
      exit;
    }
  }
}
