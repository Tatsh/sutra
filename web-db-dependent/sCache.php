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
   * The type of cache in use.
   *
   * @var string
   */
  protected $type;

  /**
   * The file path to a flat-file cache.
   *
   * @var string
   */
  private $file_path;

  /**
   * The Memcache instance if needed.
   *
   * @var Memcache
   */
  protected $memcache;

  /**
   * The Memcache host string.
   *
   * @var string
   */
  private $memcache_host;

  /**
   * The Memcache host port number.
   *
   * @var int
   */
  private $memcache_port;

  /**
   * The sCache instance.
   *
   * @var sCache
   */
  protected static $instance;

  /**
   * The current working directory.
   *
   * @var string
   */
  private static $cwd = '';

  /**
   * Throws an exception if the INI file is not readable or cannot be parsed.
   *
   * @throws fEnvironmentException If the site configuration INI file cannot
   *   be read.
   *
   * @param string $file File name.
   * @return array Array if the file could be parsed.
   */
  private static function readINIFile($file) {
    if (!is_readable($file) || ($ini = parse_ini_file($file)) === FALSE) {
      throw new fEnvironmentException('Cache configuration file could not be read');
    }
    return $ini;
  }

  /**
   * Initialises a new Memcache object if the Memcache extension is available.
   *
   * @throws fEnvironmentException If the host is invalid, if the port is
   *   invalid, or if the Memcache extension is not intalled.
   *
   * @param string $host Host to use.
   * @param integer $port Port.
   * @return Memcache
   */
  private static function initMemcache($host, $port) {
    if (!$host) {
      throw new fEnvironmentException('To use Memcache, a host (memcache_host) and a port (memcache_port) must be specified');
    }
    if (!is_numeric($port)) {
      throw new fEnvironmentException('To use Memcache, the port (memcache_port) must be an integer');
    }
    if (!class_exists('Memcache')) {
      throw new fEnvironmentException('The Memcache extension does not appear to be installed');
    }

    $memcache = new Memcache;
    $memcache->connect($host, (int)$port);

    return $memcache;
  }

  /**
   * Initialize cache based on INI file.
   *
   * @throws fEnvironmentException If the INI file cannot be read; if the
   *   cache type is invalid; if the file path for a flat file cache is
   *   invalid; if Memcache details are invalid; if Memcache is desired and
   *   Memcache extension is missing.
   *
   * @access private
   *
   * @todo Test Memcache and Xcache.
   * @todo Add support for Memcached, database and Redis.
   *
   * @return sCache The sCache object.
   */
  public function __construct() {
    $file = sConfiguration::getPath().'/cache.ini';
    $ini = self::readINIFile($file);
    $this->type = strtolower(isset($ini['type']) ? $ini['type'] : 'invalid');

    if ($this->type == 'memcache') {
      $this->memcache_host = $ini['memcache_host'];
      $this->memcache_port = $ini['memcache_port'];
      $this->memcache = self::initMemcache($this->memcache_host, $this->memcache_port);
    }
    else if ($this->type == 'apc' || $this->type == 'xcache') {
      parent::__construct($this->type);
    }
    else if ($this->type == 'file') {
      $this->file_path = $ini['file_path'];

      if (!$this->file_path) {
        throw new fEnvironmentException('To use a file for cache, a file path (file_path) must be specified');
      }

      if (strpos($this->file_path, '..'.DIRECTORY_SEPARATOR) !== FALSE ||
        strpos($this->file_path, DIRECTORY_SEPARATOR.'..') !== FALSE) {
        throw new fProgrammerException('Do not use a relative path for your cache file');
      }

      parent::__construct('file', $this->file_path);
    }
    else {
      throw new fEnvironmentException('Cache type is invalid');
    }
  }

  /**
   * Get existing instance of class.
   *
   * @return sCache
   */
  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self;
    }

    return self::$instance;
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
    if (!self::$cwd) {
      self::$cwd = getcwd();
    }

    if (is_null($class_prefix)) {
      $class_prefix = __CLASS__;
    }
    return $class_prefix.'::'.self::$cwd.'::'.$key;
  }
}
