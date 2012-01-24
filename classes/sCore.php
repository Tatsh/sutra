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
   * The debug log file name.
   *
   * @var string
   */
  private static $debug_log_filename = './dblog.txt';

  /**
   * The exception closing callback used in production mode.
   *
   * @var callback
   */
  private static $exception_closing_callback = NULL;

  /**
   * The exception closing callback parameters passed.
   *
   * @var array
   */
  private static $exception_closing_parameters = array();

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
        if (file_put_contents(self::$debug_log_filename, "\n".'Log started at '.date('Y-m-d H:i:s').":\n", FILE_APPEND)) {
          parent::registerDebugCallback(array(__CLASS__, 'debugCallback'));
          parent::enableDebugging(TRUE);
        }
        else {
          self::debug(sprintf('Debug: Cannot write to %s', self::$debug_log_filename));

          $exception_handling_type = strtolower($config->getExceptionHandlingDestinationType('string', 'email'));
          $destination = '';
          if ($exception_handling_type == 'email') {
            $domain = str_replace('http://', 'https://', fURL::getDomain());
            $destination = $config->getExceptionDestination('string', 'webmaster@'.$domain);
          }
          else {
            $site_root_name = basename(getcwd());
            $destination = $config->getExceptionDestination('string', '/var/log/sutra/'.$site_root_name.'.log');
          }

          self::enableErrorHandling($destination);
          self::enableExceptionHandling($destination, self::$exception_closing_callback, self::$exception_closing_parameters);
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
   * Set the exception closing callback, which is used in production mode
   *   and called after an exception has occurred.
   *
   * @param callback $callback Callback to use.
   * @return void
   */
  public static function setExceptionClosingCallback($callback) {
    self::$exception_closing_callback = $callback;
  }

  /**
   * Set the exception closing callback parameters.
   *
   * @param array $parameters Parameters to pass to the callback.
   * @return void
   */
  public static function setExceptionClosingCallbackParameters(array $parameters) {
    self::$exception_closing_parameters = $parameters;
  }

  /**
   * Debug callback (only registered when in not production mode).
   *
   * @internal This is never to be called directly.
   * @access private
   *
   * @param string $message Message to log.
   * @return void
   */
  public static function debugCallback($message) {
    @file_put_contents(self::$debug_log_filename, $message."\n", FILE_APPEND);
  }

  /**
   * Set the debug log file name. Do this before allowing sCore::main() to run
   *   or assing sCore::debugCallback() with fCore::registerDebugCallback().
   *
   * For a web server, the file name must have write permissions from the user
   *   that runs the web server. My recommendation on Linux (with nginx running
   *   as nginx):
   * - mkdir /var/log/sutra
   * - chmod -R 0770 /var/log/sutra
   * - chown -R nginx:nginx /var/log/sutra
   *
   * You may then want to use a format like site-name.log in /var/log/sutra
   *   (full path /var/log/site-name.log).
   *
   * @throws fProgrammerException If the log file cannot be created.
   *
   * @param string $filename Full or relative path to file. Will be created if
   *   necessary.
   * @return void
   *
   * @see sCore::main()
   * @see sCore::debugCallback()
   * @see fCore::registerDebugCallback()
   */
  public static function setDebugLogFilename($filename) {
    $time = date('Y-m-d H:i:s');

    if (!is_file($filename)) {
      if (!file_put_contents($filename, "Created file at $time\n")) {
        throw new fProgrammerException('Log file %s could not be created.', $filename);
      }
    }

    self::$debug_log_filename = $filename;
  }

  /**
   * Private to prevent external instantiation.
   *
   * @return sCore
  */
  private function __construct() {}
}
