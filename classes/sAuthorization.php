<?php
/**
 * Allows defining and checking user authentication.
 *
 * @copyright Copyright (c) 2012 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sAuthorization extends fAuthorization {
  /**
   * The administrator level name.
   *
   * @var string
   */
  private static $admin_level_name = 'admin';

  /**
   * Set the name of the administrator user level.
   *
   * @param string $name Name of the administrator authorisation level.
   * @return void
   */
  public static function setAdministratorAuthLevelName($name) {
    self::$admin_level_name = $name;
  }

  /**
   * Redirect the user if not an administrator. If the level is not named
   *   'admin', sAuthorization::setAdministratorAuthLevelName() must be called
   *   before any calls to this method.
   *
   * @param string $error_url URL to go to on error. Default is to go to the
   *   login page.
   * @return void
   * @see sAuthorization::setAdministratorAuthLevelName()
   */
  public static function requireAdministratorPrivileges($error_url = NULL) {
    $not_admin = !self::checkLoggedIn() || self::getUserAuthLevel() != self::$admin_level_name;

    parent::requireLoggedIn();

    if ($not_admin) {
      fURL::redirect($error_url ? $error_url : self::getLoginPage());
    }
  }

  /**
   * Require that a user not be logged in.
   *
   * @return void
   */
  public static function requireNotLoggedIn() {
    if (self::checkLoggedIn()) {
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
