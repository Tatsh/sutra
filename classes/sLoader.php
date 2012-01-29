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
   * Path where Sutra model classes are installed.
   *
   * @var string
   */
  private static $model_classes_path = '';

  /**
   * Path where Sutra router classes are installed.
   *
   * @var string
   */
  private static $router_classes_path = '';

  /**
   * Class prefixes.
   *
   * @var array
   */
  private static $prefixes = NULL;

  /**
   * All the current Sutra classes.
   *
   * @var array
   */
  private static $classes = array(
    'sProcessException',
    'sAuthorization',
    'sCache',
    'sConfiguration',
    'sCore',
    'sDatabase',
    'sHTML',
    'sImage',
    'sJSONP',
    'sMessaging',
    'sNumber',
    'sPostRequest',
    'sPostRequestProcessor',
    'sProcess',
    'sRouter',
    'sTemplate',
    'sTemplateVariableSetter',
    'sTimestamp',
    'sAJAXResponder',
  );

  /**
   * All the model classes that come with Sutra.
   *
   * @var array
   */
  private static $model_classes = array(
    'Category',
    'CompiledJavascriptFile',
    'ContactMailMessage',
    'ResetPasswordRequest',
    'RouterAlias',
    'SiteVariable',
    'User',
    'UserVerification',
  );

  /**
   * All of Sutra's router classes.
   *
   * @var array
   */
  private static $router_classes = array(
    'AccountActionController',
    'AdminActionController',
    'AdminUserListActionController',
    'ContactActionController',
    'CSRFTokenController',
    'CSSActionController',
    'FrontActionController',
    'LoginActionController',
    'LogoutActionController',
    'PageNotFoundActionController',
    'RegisterActionController',
    'ResetPasswordActionController',
    'UserProfileActionController',
    'UserVerificationActionController',
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
   * Load the main core classes.
   *
   * @return void
   */
  public static function requireClasses() {
    if (class_exists('sCore')) {
      return;
    }

    self::setPaths();
    foreach (self::$classes as $class) {
      require self::$path.$class.'.php';
    }
  }

  /**
   * Load the model classes.
   *
   * @return void
   */
  public static function requireModelClasses() {
    if (class_exists('User')) {
      return;
    }

    self::setPaths();
    self::requireClasses();

    foreach (self::$model_classes as $class) {
      require self::$model_classes_path.$class.'.php';
    }
  }

  /**
   * Load the router classes.
   *
   * @return void
   */
  public static function requireRouterClasses() {
    if (class_exists('AccountActionController')) {
      return;
    }

    self::setPaths();
    self::requireModelClasses();

    foreach (self::$router_classes as $class) {
      require self::$router_classes_path.$class.'.php';
    }
  }

  /**
   * Override eager() method to load Sutra classes after Flourish's.
   *
   * @return void
   */
  public static function eagar() {
    parent::eager();
    self::setPaths();
    self::requireRouterClasses();
  }

  /**
   * Get the path to the main router classes.
   *
   * @internal Used by sRouter.
   *
   * @return string A path.
   */
  public static function getRoutesPath() {
    self::setPaths();
    return self::$router_classes_path;
  }

  /**
   * Get the path to the main model classes.
   *
   * @internal Used by the schema installer.
   *
   * @return string A path.
   */
  public static function getModelClassesPath() {
    self::setPaths();
    return self::$model_classes_path;
  }

  /**
   * Determines where Sutra is installed.
   *
   * @return void
   */
  private static function setPaths() {
    if (!self::$path) {
      $path = self::$path = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
      self::$router_classes_path = realpath($path.'..'.DIRECTORY_SEPARATOR.'routers').DIRECTORY_SEPARATOR;
      self::$model_classes_path =  realpath($path.'..'.DIRECTORY_SEPARATOR.'model').DIRECTORY_SEPARATOR;
    }
  }
}
