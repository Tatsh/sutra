<?php
/**
 * Manages loading of classes.
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
class sLoader extends fLoader {
  /**
   * Path where Sutra is installed.
   *
   * @var string
   */
  private static $path = '';

  /**
   * All the Sutra classes.
   *
   * @var array
   */
  private static $classes = array(
    'sArray',
//     'sAuthorization',
    'sCache',
    'sGrammar',
    'sHTML',
    'sImage',
    'sJSONP',
    'sMessaging',
//     'sNumber',
    'sORMJSON',
//     'sPostRequest',
    'sProcess',
    'sProcessException',
    'sTemplate',
    'sTimestamp',
  );

  /**
   * Override best() method.
   *
   * @return void
   * @see sLoader::eagar()
   */
  public static function best() {
    if (self::hasOpcodeCache()) {
      return sLoader::eagar();
    }

    self::lazy();
  }

  /**
   * Creates constructor functions. This makes it possible to write:
   *
   * <code>new sProcess('node myscript')->execute();</code>
   *
   * @return void
   */
  private static function createConstructorFunctions() {
    if (function_exists('sImage')) {
      return;
    }

    function sImage($file_path, $skip_checks = FALSE) {
      return new sImage($file_path, $skip_checks);
    }

    // Limited signature support
    function sProcess($name) {
      return new sProcess($name);
    }

    function sTimestamp($datetime, $timezone = NULL) {
      return new sTimestamp($date, $timezone);
    }
  }

  /**
   * Override eager() method to load Sutra classes after Flourish's.
   *
   * @return void
   */
  public static function eagar() {
    parent::eager();

    self::setPath();
    self::createConstructorFunctions();

    foreach (self::$classes as $class) {
      require self::$path.$class.'.php';
    }
  }

  /**
   * Determines where Sutra is installed.
   *
   * @return void
   */
  private static function setPath() {
    if (!self::$path) {
      self::$path = realpath(dirname(__FILE__)).'/';
    }
  }

  /**
   * Registers a class auto-loader to load Sutra classes.
   *
   * @return void
   */
  public static function lazy() {
    parent::lazy();

    self::setPath();
    self::createConstructorFunctions();

    spl_autoload_register(array('sLoader', 'autoload'));
  }

  /**
   * Tries to load a Sutra class.
   *
   * @internal
   *
   * @param  string $class The class to load.
   * @return void
   */
  public static function autoload($class) {
    if ($class[0] != 's' || ord($class[1]) < 65 || ord($class[1]) > 90) {
      return;
    }

    if (!in_array($class, self::$classes)) {
      return;
    }

    require self::$path.$class.'.php';
  }
}
