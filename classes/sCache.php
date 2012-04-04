<?php
/**
 * Singleton class to manage Sutra-specific cache.
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
class sCache extends fCache {
  /**
   * The current working directory.
   *
   * @var string
   */
  private static $cwd = '';

  /**
   * Initialises the class.
   *
   * @return void
   */
  private static function initialize() {
    if (!self::$cwd) {
      self::$cwd = getcwd();
    }
  }

  /**
   * Get a key unique to the site.
   *
   * @param string $key Key to use.
   * @param string $class_prefix Class prefix to use. If not specified, sCache
   *   will be used.
   * @return string Key that can be used for cache storage.
   */
  public static function getSiteUniqueKey($key, $class_prefix = NULL) {
    self::initialize();

    if (is_null($class_prefix)) {
      $class_prefix = __CLASS__;
    }

    return $class_prefix.'::'.self::$cwd.'::'.$key;
  }
}
