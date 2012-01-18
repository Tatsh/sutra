<?php
/**
 * Manages auto-loading of classes. Extends fLoader class.
 *
 * Systems that do not have case-sensitive file names can pose a problem for
 *   auto-loading. In such a case, the easiest work-around is to rename the
 *   class.
 *
 * Example: autoloader_function('sCoreFunctionality') -> 'sCore/Functionality.php'
 *   On OS X (by default) and Windows, score/functionality.php is the same file.
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
   * Override best() method. We do not return after calling eagar()
   *   because we still want the autoloader to be registered.
   *
   * @return void
   */
  public static function best() {
    self::eagar();
    spl_autoload_register(array('sLoader', 'autoload'));
  }

  /**
   * Override eager() method to load Sutra classes after Flourish's.
   *
   * @return void
   */
  public static function eagar() {
    parent::eager();
    self::setPaths();

    foreach (self::$classes as $class) {
      require self::$path.$class.'.php';
    }

    foreach (self::$model_classes as $class) {
      require self::$model_classes_path.$class.'.php';
    }

    foreach (self::$router_classes as $class) {
      if (!class_exists($class)) {
        require self::$router_classes_path.$class.'.php';
      }
    }
  }

  /**
   * Auto-loader callback to load 3rd party classes.
   *
   * @internal This is never to be called directly.
   * @access private
   *
   * @param string $name The class to load
   * @return void
   */
  public static function autoload($name) {
    $file = './3rdparty/routers/'.$name.'.php';
    if (is_readable($file)) {
      require $file;
      return;
    }

    $file = './3rdparty/model/'.$name.'.php';
    if (is_readable($file)) {
      require $file;
      return;
    }

    $file = './3rdparty/lib/'.$name.'.php';
    if (is_readable($file)) {
      require $file;
      return;
    }
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
   * Determines where Sutra is installed.
   *
   * @return void
   */
  private static function setPaths() {
    if (!self::$path) {
      self::$path = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
      self::$router_classes_path = realpath(self::$path.'..'.DIRECTORY_SEPARATOR.'routers').DIRECTORY_SEPARATOR;
      self::$model_classes_path =  realpath(self::$path.'..'.DIRECTORY_SEPARATOR.'model').DIRECTORY_SEPARATOR;
    }
  }
}
