<?php
/**
 * Allows defining and checking user authentication.
 *
 * @copyright Copyright (c) 2012 bne1.
 * @author Andrew Udvare [au] <andrew@bne1.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sAuthorization extends fAuthorization {
  const requireNotLoggedIn = 'sAuthorization::requireNotLoggedIn';

  /**
   * Require that a user not be logged in.
   *
   * @param string $redirect Where to redirect if the user is logged in.
   * @return void
   */
  public static function requireNotLoggedIn($redirect = '/') {
    if (self::checkLoggedIn()) {
      fURL::redirect($redirect);
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
