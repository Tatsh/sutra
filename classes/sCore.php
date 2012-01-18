<?php
/**
 * Base class for general functionality. Entry point is here.
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
class sCore extends fCore {
  /**
   * Entry point.
   *
   * If production mode is not on, and ./dblog.txt is writable,
   *   sCore::debugCallback() will be registered with
   *   fCore::registerDebugCallback().
   *
   * One way to make ./dblog.txt writable is to set its owner and group to the
   *   web server's and set the permission level to 0046. This way, if you are
   *   in the web server group, you can read the file, but you cannot modify
   *   the file.
   *
   * Gentoo with nginx: chown nginx:nginx dblog.txt && chmod 0046 dblog.txt
   * Mac with Apache built-in: chown _www:_www dblog.txt && chmod 0046 dblog.txt
   *
   * @throws fProgrammerException fAuthorizationExceptions and
   *   fEnvironmentExceptions are converted to fProgrammerExceptions.
   *
   * @return void
   *
   * @see fCore::registerDebugCallback()
   * @see sCore::debugCallback()
   */
  public static function main() {
    try {
      sCache::getInstance();
      sDatabase::getInstance();
      $config = sConfiguration::getInstance();

      if (!$config->getProductionModeOn('bool')) {
        if (file_put_contents('./dblog.txt', 'Log started at '.date('Y-m-d H:i:s').":\n")) {
          parent::registerDebugCallback(array(__CLASS__, 'debugCallback'));
          parent::enableDebugging(TRUE);
        }
        else {
          self::debug('Cannot write to ./dblog.txt');
        }
      }

      sAuthorization::initialize();
    }
    catch (fAuthorizationException $e) {
      $error = 'Caught fAuthorizationException: '.strip_tags($e->getMessage());
      throw new fProgrammerException($error);
    }
    catch (fEnvironmentException $e) {
      $error = 'Caught fEnvironmentException: '.strip_tags($e->getMessage());
      throw new fProgrammerException($error);
    }

    sPostRequest::handle(); // Process a POST (possibly form) request if one was made
    sTemplate::setActiveTemplate($config->getTemplate());
    sRouter::handle(); // Process the page request
  }

  /**
   * Debug callback only used when in not production mode.
   *
   * Filters out 'Query time was', 'BEGIN', and 'QUERY' lines.
   *
   * For this function to work a file './dblog.txt' must be at root with
   *   proper permissions (such as 777 on Unix-like).
   *
   * @internal This is never to be called directly.
   * @access private
   *
   * @param string $message Message sent.
   * @return void
   */
  public static function debugCallback($message) {
    $filter = array(
      'BEGIN',
      'COMMIT',
    );

    $message = preg_replace('/Query\stime\swas\s\d+\.\d+\sseconds\sfor\:'."\n".'/', '', $message);
    $lines = explode("\n", $message);

    foreach ($lines as $line) {
      $line = trim($line);
      if (!in_array($line, $filter)) {
        @file_put_contents('./dblog.txt', $message."\n", FILE_APPEND);
      }
    }
  }

  /**
   * Private to prevent external instantiation.
   *
   * @return sCore
  */
  private function __construct() {}
}
