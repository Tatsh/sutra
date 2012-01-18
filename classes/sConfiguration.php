<?php
/**
 * Manages Sutra-specific configuration. Configuration is done by hand
 *   currently in the ./config/site.ini file. This file must exist for the
 *   site to function. Use ./config/site.sample.ini as a template.
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
class sConfiguration {
  /**
   * The sConfiguration instance.
   *
   * @var sConfiguration
   */
  protected static $instance;

  /**
   * For use with CLI. Cached values since APC might not work.
   *
   * @var array
   */
  protected static $values = array();

  /**
   * The working directory.
   */
  private static $cwd = '/var/fake/root';

  /**
   * Constructor. Private to prevent external instantiation.
   *
   * @throws fEnvironmentException If the site configuration INI file cannot
   *   be read.
   *
   * @return sConfiguration
   */
  private function __construct() {
    $file = './config/site.ini';
    self::$cwd = getcwd();

    if (is_readable($file)) {
      $ini = parse_ini_file($file);
      $cache = sCache::getInstance();

      $settings = array(
        'site_name',
        'site_slogan',
        'base_url',
        'cdn_enabled',
        'cdn_urls',
        'cookie_domain',
        'production_mode_on',
        'display_mobile_tags',
        'template',
        'google_analytics_u_a',
        'mollom_public_key',
        'mollom_secret',
        'facebook_app_id',
        'bitly_api_key',
        'bitly_api_username',
        'site_text_direction',
        'site_language',
        'favicon_path',
        'site_logo_path',
        'site_timezone',
        'mollom_enabled',
        'closure_jar',
        'site_u_r_i',
        'allowed_email_domains',
        'facebook_app_secret',
      );

      foreach ($settings as $setting) {
        if (isset($ini[$setting])) {
          // Expire setting after 1 hour
          $key = __CLASS__.'::'.self::$cwd.'::'.$setting;
          $cache->set($key, $ini[$setting], 3600);
          self::$values[$setting] = $ini[$setting];

//           $key = __CLASS__.'::'.$setting;
//           SiteVariable::setValue($key, $ini[$setting], 3600);
        }
      }

      $cache->set(__CLASS__.'::'.self::$cwd.'::site_settings_last_cached', time(), 3600);
    }
    else {
      throw new fEnvironmentException('Site configuration file could not be read.');
    }
  }

  /**
   * Add a setting only if it does not already exist.
   *
   * @param string $key Key name.
   * @param mixed $value Value.
   * @param int $ttl Time to live.
   * @return void
   */
  public static function add($key, $value, $ttl = 3600) {
    $key = __CLASS__.'::'.self::$cwd.'::'.$key;
    sCache::getInstance()->add($key, $value, $ttl);
  }

  /**
   * Get a setting.
   *
   * @param string $key Key name.
   * @param mixed $default Default value to return if the key does not exist.
   * @param string $cast If specified, cast the value before returning it.
   *   One of 'int', 'integer', 'unset', 'bool', 'boolean', 'float',
   *   'double', 'real', 'string', 'array', 'object'. If you are
   *   possibly expecting an object, do not cast it to float as this
   *   will generate an E_NOTICE messsage.
   * @return mixed Value of key or NULL.
   */
  public static function get($key, $default = NULL, $cast = NULL) {
    $cache = sCache::getInstance();
    $key = __CLASS__.'::'.self::$cwd.'::'.$key;
    $value = $cache->get($key, $default);

    if (is_null($value)) {
      // Value may have expired; recache
      self::$instance = new self;
      $value = $cache->get($key, $default);
    }

    if (!is_null($cast)) {
      $cast = strtolower($cast);
      switch ($cast) {
        case 'int':
        case 'integer':
          return (int)$value;

        case 'unset':
          return NULL;

        case 'bool':
        case 'boolean':
          return (bool)$value;

        case 'float':
        case 'double':
        case 'real':
          return (float)$value;

        case 'string':
          return strval($value);

        case 'array':
          return (array)$value;

        case 'object':
          return (object)$value;
      }
    }

    return $value;
  }

  /**
   * Set a value, overwriting any existing value.
   *
   * @param string $key Key name.
   * @param mixed $value Value.
   * @param int $ttl Time to live. Default is 1 hour.
   * @return void
   */
  public static function set($key, $value, $ttl = 3600) {
    $key = __CLASS__.'::'.self::$cwd.'::'.$key;
    sCache::getInstance()->set($key, $value, $ttl);
  }

  /**
   * Get instance of class.
   * @return sConfiguration
   */
  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  /**
   * Get a value with camelCase notation. Optional arguments:
   * - index 0 - string - cast value. One of: 'int', 'integer', 'unset', 'bool',
   *   'boolean', 'float', 'double', 'real', 'string', 'array', 'object'.
   * - index 1 - mixed - Default value to return if the value does not exist.
   *
   * @param string $method Such as: getSiteName, getSiteSlogan.
   * @return mixed Value or NULL.
   */
  public function __call($method, $arguments) {
    static $sapi;

    if (!$sapi) {
      $sapi = strtolower(php_sapi_name());
    }

    if (substr($method, 0, 3) === 'get') {
      $method = substr($method, 3);
      $config = self::getInstance();

      $key = fGrammar::underscorize($method);
      $arguments[1] = isset($arguments[1]) ? strtolower($arguments[1]) : '';
      $types = array('int', 'integer', 'unset', 'bool', 'boolean', 'float', 'double', 'real', 'string', 'array', 'object');

      if ($sapi !== 'cli') {
        if (in_array($arguments[1], $types)) {
          return $config->get($key, isset($arguments[0]) ? $arguments[0] : NULL, $arguments[1]);
        }
        else {
          return $config->get($key, isset($arguments[0]) ? $arguments[0] : NULL);
        }
      }
      else {
        // CLI, no casting
        return self::$values[$key];
      }
    }
  }
}
