<?php
/**
 * Manages Sutra-specific configuration. Configuration is done by hand
 *   currently in the ./config/site.ini file. This file must exist for the
 *   site to function. Use ./config/site.sample.ini as a template.
 *
 * After the file is read, it will not be re-read until cache is cleared.
 *   You can perform this action simply by visiting /admin/clear-cache as user
 *   with the 'admin' auth_level.
 *
 * This class is simply a front end to SiteVariable. Every call to set or get
 *   a variable will call SiteVariable's version, but with the proper key name.
 *   sConfiguration->getNameOfValue() is equivalent to calling
 *   SiteVariable::getValue('sConfiguration::name_of_value').
 *
 * @todo Make file not specifically at ./config/site.ini.
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
   *
   * @var string
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
    $recache = FALSE;

    $cache = sCache::getInstance();
    if (!$cache->get(__CLASS__.'::'.self::$cwd.'::site_settings_last_cached')) {
      $recache = TRUE;
    }

    if (is_readable($file) && $recache) {
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
          SiteVariable::setValue(self::getValidKeyName($setting), $ini[$setting], 3600);
        }
      }

      $cache->set(__CLASS__.'::'.self::$cwd.'::site_settings_last_cached', time(), 3600);
    }
    else if (!$recache) {
      return;
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
    SiteVariable::setValue(self::getValidKeyName($key), $value, $ttl);
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
    return SiteVariable::getValue(self::getValidKeyName($key), $cast, $default);
  }

  /**
   * Get the valid key name for a key without prefix.
   *
   * @param string $key Key name without prefix.
   * @return string $key Key with prefix.
   */
  private static function getValidKeyName($key) {
    return __CLASS__.'::'.$key;
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
    SiteVariable::setValue(self::getValidKeyName($key), $value, $ttl);
  }

  /**
   * Get an instance of the class.
   *
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
   * @param string $method Method such as: getSiteName, getSiteSlogan.
   * @return mixed Value or NULL.
   */
  public function __call($method, $arguments) {
    if (substr($method, 0, 3) === 'get') {
      $method = substr($method, 3);
      $config = self::getInstance();

      $key = fGrammar::underscorize($method);
      $arguments[1] = isset($arguments[1]) ? strtolower($arguments[1]) : '';

      return $config->get($key, isset($arguments[0]) ? $arguments[0] : NULL, $arguments[1]);
    }

    return NULL;
  }
}
