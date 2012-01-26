<?php
/**
 * Manages Sutra-specific configuration. Configuration is done by hand
 *   currently in the CONF_PATH/site.ini file. This file must exist for the
 *   site to function. Use ./config/site.sample.ini as a template.
 *
 * Configuration files can be anywhere. Just call sConfiguration::setPath()
 *   before sCore::main() (or your own main) runs.
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
   * The path where the configuration files are, including database.ini.
   *
   * @var string
   */
  private static $configuration_files_path = './config';

  /**
   * Constructor. Private to prevent external instantiation.
   *
   * @throws fEnvironmentException If the site configuration INI file cannot
   *   be read.
   *
   * @return sConfiguration
   */
  private function __construct() {
    $file = self::$configuration_files_path.DIRECTORY_SEPARATOR.'site.ini';
    self::$cwd = getcwd();
    $recache = FALSE;

    $cache = sCache::getInstance();
    if (!$cache->get(__CLASS__.'::'.self::$cwd.'::site_settings_last_cached')) {
      $recache = TRUE;
    }

    if (is_readable($file) && $recache) {
      $ini = parse_ini_file($file);
      $cache = sCache::getInstance();

      $defaults = array(
        'site_name' => __('No Name'),
        'site_slogan' => __('No Slogan'),
        'cdn_urls' => array(),
        'production_mode_on' => FALSE,
        'template' => 'default',
        'google_analytics_u_a' => 'UA-000000-00',
        'site_text_direction' => 'ltr',
        'site_language' => 'en',
        'site_timezone' => 'Europe/London', // GMT
        'allowed_email_domains' => array(),
        'disallowed_email_domains' => array(),
      );

      $settings_to_type = array(
        'site_name' => 'string',
        'site_slogan' => 'string',
        'cdn_urls' => 'array',
        'production_mode_on' => 'bool',
        'template' => 'string',
        'google_analytics_u_a' => 'string',
        'site_text_direction' => 'string',
        'site_language' => 'string',
        'site_timezone' => 'string',
        'allowed_email_domains' => 'array',
        'disallowed_email_domains' => 'array',
      );

      foreach ($settings_to_type as $setting_key => $type) {
        if (isset($ini[$setting_key])) {
          $value = $ini[$setting_key];
          unset($ini[$setting_key]);

          switch ($type) {
            case 'string':
              $value = (string)$value;
              break;

            case 'array':
              $value = (array)$value;
              break;

            case 'boolean':
            case 'bool':
              $value = (bool)$value;
              break;
          }

          SiteVariable::setValue(self::getValidKeyName($setting_key), $value, 3600);
        }
        else {
          SiteVariable::setValue(self::getValidKeyName($setting_key), $defaults[$setting_key], 3600);
        }
      }

      // Set the rest; there could be custom settings
      foreach ($ini as $key => $value) {
        SiteVariable::setValue(self::getValidKeyName($key), $value, 3600);
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
   * Set where the ini files are.
   *
   * @param string $path A path. Will be converted to a regular path.
   * @return void
   */
  public static function setPath($path) {
    new fDirectory($path);
    self::$configuration_files_path = realpath($path);
  }

  /**
   * Get where the ini files are.
   *
   * @return string The path.
   */
  public static function getPath() {
    return self::$configuration_files_path;
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
   * @internal Called by sCore::main().
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
   *
   * @todo Make sure this still works with CLI.
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
