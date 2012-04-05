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
   * Override best() method. Alias for sLoader::eagar().
   *
   * @return void
   * @see sLoader::eagar()
   */
  public static function best() {
    self::eagar();
  }

  /**
   * Override eager() method to load Sutra classes after Flourish's.
   *
   * @return void
   */
  public static function eagar() {
    parent::eager();
    self::setPath();
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
}
