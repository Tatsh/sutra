<?php
/**
 * Manages Sutra-specific authentication.
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
class sAuthorization extends fAuthorization {
  /**
   * Whether or not this class is initialized.
   *
   * @var boolean
   */
  private static $initialized = FALSE;

  /**
   * The guest user ID.
   *
   * @var integer
   */
  private static $guest_user_id = NULL;

  /**
   * If the URL requested is to a resource, such as an image or JavaScript
   *   file.
   *
   * @return boolean If the URL requested is to a resource.
   */
  private static function isResource() {
    $extensions = array(
      'css',
      'js',
      'png',
      'gif',
      'jpeg',
      'jpg',
      'bmp',
      'wbmp',
    );
    $url = fURL::get();
    $offsets = array(-3, -4, -5);

    foreach ($offsets as $offset) {
      $extension = substr($url, $offset);
      if ($extension[0] === '.') {
        break;
      }
    }

    if ($extension[0] !== '.') {
      return FALSE;
    }

    return (bool)preg_match('/'.implode('|', $extensions).'/', $extension);
  }

  /**
   * Initialise the class.
   *
   * @return void
   *
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public static function initialize() {
    if (self::isResource()) {
      self::$initialized = TRUE;
      return;
    }

    if (!self::$initialized) {
      try {
        $session_length = SiteVariable::getValue(__CLASS__.'::session_length', NULL, '30 minutes', 0);
      }
      catch (fProgrammerException $e) {
        sDatabase::getInstance();
      }

      $session_length = SiteVariable::getValue(__CLASS__.'::session_length', NULL, '30 minutes', 0);
      $long_session_length = SiteVariable::getValue(__CLASS__.'::long_session_length', NULL, '1 week', 0);
      $login_page = SiteVariable::getValue(__CLASS__.'::login_page', NULL, '/login', 0);
      $is_persistent = SiteVariable::getValue(__CLASS__.'::is_persistent', 'bool', FALSE, 0);

      fSession::setLength($session_length, $long_session_length);
      fSession::setBackend(sCache::getInstance());
      if ($is_persistent) {
        fSession::enablePersistence();
      }

      $default_auth_levels = array('admin' => 100, 'user' => 50, 'guest' => 25);
      $auth_levels = SiteVariable::getValue(__CLASS__.'::auth_levels', 'array', $default_auth_levels);

      self::getGuestUserId();
      self::setLoginPage($login_page);
      self::setAuthLevels($auth_levels);

      self::$initialized = TRUE;
    }
  }

  /**
   * Get the guest user ID.
   *
   * @return integer The guest user ID.
   *
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public static function getGuestUserId() {
    if (is_null(self::$guest_user_id)) {
      $cache = sCache::getInstance();
      $key = __CLASS__.'::'.getcwd().'::guest_user_id';
      self::$guest_user_id = $id = (int)$cache->get($key);

      if (is_null($id)) {
        try {
          $guest = new User(array('name' => 'guest'));
          self::$guest_user_id = $guest->getUserId();
          $cache->set($key, self::$guest_user_id);
        }
        catch (fNotFoundException $e) {
          throw new fAuthorizationException('This cannot work without a guest account present.');
        }
      }
    }

    return self::$guest_user_id;
  }

  /**
   * Redirect the user if not an administrator.
   *
   * @param boolean $ajax If set to TRUE, this will test if the request is made
   *   via AJAX and then print a JSON-encoded message instead.
   *
   * @return void
   */
  public static function requireAdministratorPrivileges($ajax = FALSE) {
    if (($ajax && fRequest::isAjax()) ||($ajax && fRequest::get('ajax', 'boolean'))) {
      print fJSON::encode(array('error' => __('This resource is not available to your user level.')));
      exit;
    }

    parent::requireLoggedIn();

    if (parent::getUserAuthLevel() != 'admin') {
      $page_404 = SiteVariable::getValue(__CLASS__.'::page_404', 'string', '/404', 0);
      fURL::redirect($page_404);
      return;
    }
  }

  /**
   * Require that a user not be logged in.
   *
   * @param boolean $handle_ajax If set to TRUE, then AJAX requests will be
   *   handled. Default is FALSE.
   * @return void
   */
  public static function requireNotLoggedIn($handle_ajax = FALSE) {
    if (self::checkLoggedIn()) {
      if ($handle_ajax) {
        print fJSON::encode(array('error' => __('You are already logged in.')));
        exit;
      }

      fURL::redirect('/');
    }
  }

  // @codeCoverageIgnoreStart
  /**
   * Forces use as a static class
   *
   * @return sAuthorization
   */
  private function __construct() {}
  // @codeCoverageIgnoreEnd
}
