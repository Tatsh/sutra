<?php
/**
 * This class is optional to use. It is generally for use as the core class of
 *   the site.
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
abstract class sCore extends fCore {
  /**
   * The cache.
   *
   * @var sCache|fCache
   */
  protected static $cache = NULL;

  /**
   * The fDatabase instance.
   *
   * @var fDatabase
   */
  protected static $db = NULL;

  /**
   * Get the site cache. Can use sCache or fCache. They are both compatible.
   *
   * @return sCache|fCache The fCache/sCache instance.
   */
  protected static function getCache() {
    throw new fProgrammerException('The function, "%s", must be implemented.', __CLASS__.'::'.__FUNCTION__);
  }

  /**
   * Get the site database instance. This is useful for getting database
   *   information or using SQL queries with methods such as
   *   translatedExcecute.
   *
   * This method can also be used to configure fORM if that is desired.
   *
   * @return fDatabase The fDatabase instance.
   * @see fDatabase::translatedExcecute()
   * @see fORMDatabase::attach()
   */
  protected static function getDatabase() {
    throw new fProgrammerException('The function, "%s", must be implemented.', __CLASS__.'::'.__FUNCTION__);
  }

  /**
   * Configures session settings.
   *
   * @return void
   * @see fSession::setLength()
   * @see fSession::setBackend()
   */
  protected static function configureSession() {
    $method = get_called_class().'::getCache';
    fSession::setLength('30 minutes', '1 week');
    fSession::setBackend(fCore::call($method));
  }

  /**
   * Configures authorisation.
   *
   * @return void
   * @see fAuthorization::setAuthLevels()
   * @see fAuthorization::setLoginPage()
   */
  protected static function configureAuthorization() {
    sAuthorization::setAuthLevels(array('admin' => 100, 'user' => 50, 'guest' => 25));
    sAuthorization::setLoginPage('/login');
  }

  /**
   * Example entry point. This would be called from index.php or similar file.
   *
   * Calls to set up the database, configure session, configure authorisation,
   *   and set up the exception handler.
   *
   * After these calls, it's expected that the template (sTemplate) will be
   *   set up and that a router (such as Moor) will be used to continue the
   *   request.
   *
   * @return void
   * @see fCore::getDatabase()
   * @see fCore::configureSession()
   * @see fCore::configureAuthorization()
   */
  public static function main() {
    $class = get_called_class();
    self::call($class.'::getDatabase');
    self::call($class.'::configureSession');
    self::call($class.'::configureAuthorization');
  }
}
