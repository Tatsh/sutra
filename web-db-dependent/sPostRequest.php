<?php
/**
 * Manages POST requests.
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
class sPostRequest {
  /**
   * The key in session that holds the last POST values. The last POST values
   *   are only stored when a validation error occurs.
   *
   * @var string
   */
  const LAST_POST_SESSION_KEY = 'sPostRequest::last_post';

  /**
   * Path prefixes that do not need CSRF checking.
   *
   * @var array
   */
  private static $no_csrf_path_prefixes = array(
    '/json', // may disappear later
  );

  /**
   * Registered callbacks.
   *
   * @var array
   */
  private static $registered_callbacks = array(
    'after' => array('*' => array()),
    'before' => array('*' => array()),
  );

  /**
   * Call the processor classes for this URL.
   *
   * @return void
   */
  public static function callProcessorClasses() {
    $url = fURL::get();

    foreach (get_declared_classes() as $class) {
      $reflect = new ReflectionClass($class);
      if ($reflect->implementsInterface('sPostRequestProcessor')) {
        $urls = fCore::call($class.'::getURLs');
        if (in_array($url, $urls)) {
          fCore::call($class.'::submit');
        }
      }
    }
  }

  /**
   * Add a path prefix that does not need a CSRF check.
   *
   * @param string $path Path prefix with leading / and optional ending /.
   * @return void
   */
  public static function addNoCSRFPrefix($path) {
    self::$no_csrf_path_prefixes[] = $path;
  }

  /**
   * Register a callback to be called after before or after validation.
   *   Callbacks registered should have no knowledge of other registered
   *   callbacks.
   *
   * @param callback $callback Callback.
   * @param string $when When to call. One of: 'after' for after successful
   *   validation,'before' for before any processing by the routing method.
   * @param string $url URL (beginning with /) that this will be called for. By
   *   default, this is all URLs.
   * @return void
   */
  public static function registerCallback($callback, $when = 'after', $url = '*') {
    $when = strtolower($when);

    if ($when !== 'after' && $when !== 'before') {
      throw new fProgrammerException('Invalid when value specified, "%s". Must be one of: before, after.', $when);
    }

    self::$registered_callbacks[$when][] = $callback;
  }

  /**
   * Calls registered callbacks.
   *
   * @param string $when One of: 'after', 'before'.
   * @return void
   */
  private static function callCallbacks($when) {
    foreach (self::$registered_callbacks[$when] as $callback) {
      $callback();
    }
  }

  /**
   * Handles AJAX requests.
   *
   * @return void
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  protected static function ajaxHandle() {
    try {
      $url = fURL::get();
      fJSON::sendHeader();
      self::handleCommon();
      // If any data needed to be sent it should've been printed in the callback
    }
    catch (fValidationException $e) {
      $ret = array(
        'error' => self::getErrorMessageFromException($e),
        'csrf' => fRequest::generateCSRFToken($url),
      );
      print fJSON::encode($ret);
    }
    exit;
  }

  /**
   * Checks if the path in use requires a CSRF.
   *
   * @param string $url URL to check.
   * @return boolean If the path requires a CSRF (default TRUE).
   */
  protected static function requiresCSRF($url = NULL) {
    if (!$url) {
      $url = fURL::get();
    }

    foreach (self::$no_csrf_path_prefixes as $prefix) {
      if (substr($url, 0, strlen($prefix)) === $prefix) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Runs Moor's router.
   *
   * @return void
   */
  private static function useMoor() {
    sRouter::getRoutes();
    Moor::run();
  }

  /**
   * The common POST handling processing code.
   *
   * @return void
   */
  private static function handleCommon() {
    fRequest::validateCSRFToken(fRequest::get('csrf', 'string'));
    self::useMoor();
    self::callProcessorClasses();
    self::deleteLastPOSTValues();
  }

  /**
   * Gets the error message from the exception with tags and new lines
   *   stripped, handling special cases as well.
   *
   * @param fValidationException $e Exception to use.
   * @return string
   */
  private static function getErrorMessageFromException($e) {
    $error = __(strip_tags($e->getMessage()));
    $error = preg_replace("#(\n)+#", ' ', $error);

    if (strpos($error, 'The form submitted could not be validated as authentic') !== FALSE) {
      $error = __('An unknown error occurred. Please try again.');
    }

    return $error;
  }

  /**
   * Called from sCore::main(). If this is not a POST request, this function
   *   will return immediately. Otherwise, POST data will be processed and the
   *   browser will be redirected.
   *
   * On error, POST values are stored in the cache key
   *   'sPostRequest::last_post'.
   *
   * If the URL is exempt from CSRF validation, then this class will simply
   *   run Moor::run() and exit. If more processing is required based on
   *   classes that implement the sPostRequestProcessor interface, it is up
   *   to the routing method to call sPostRequest::callProcessorClasses().
   *
   * @return void
   * @see sPostRequest::callProcessorClasses()
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  public static function handle() {
    try {
      if (!fRequest::isPost()) {
        return;
      }

      self::callCallbacks('before');

      if (fRequest::isAjax()) {
        self::ajaxHandle();
      }

      $url = fURL::get();

      if (!self::requiresCSRF($url)) {
        self::useMoor();
        exit;
      }

      self::handleCommon();

      self::callCallbacks('after');

      $destination = fRequest::get('destination', 'string', '/');
      $destination = fAuthorization::getRequestedURL(TRUE, $destination);
      fURL::redirect($destination);
    }
    catch (fValidationException $e) {
      self::savePOSTValues();
      sMessaging::addError(self::getErrorMessageFromException($e), self::getDestinationURL());
      fURL::redirect($destination);
    }
  }

  /**
   * Saves the POST values to sessions.
   *
   * @return void
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  private static function savePOSTValues() {
    $safe_post = array();
    foreach ($_POST as $key => $value) {
      $safe_post[$key] = fRequest::get($key);
    }
    fSession::set(self::LAST_POST_SESSION_KEY, $safe_post);
  }

  /**
   * Gets the destination URL.
   *
   * @return string The destination URL.
   */
  private static function getDestinationURL() {
    // Redirect back to same page without /post
    $url = explode('/', fURL::get());
    $destination = array();

    $count = sizeof($url) - 1;
    $i = 0;
    foreach ($url as $part) {
      if ($part === 'post' && $i == $count) {
        break;
      }

      $destination[] = $part;

      $i++;
    }

    return implode('/', $destination);
  }

  /**
   * Restore last POST values so that they may be used in form generation.
   *
   * @return void
   */
  public static function restoreLastPOSTValues() {
    $values = fSession::get(self::LAST_POST_SESSION_KEY, array());
    foreach ($values as $key => $value) {
      fRequest::set($key, $value);
    }
  }

  /**
   * Deletes all POST values stored.
   *
   * @return void
   */
  public static function deleteLastPOSTValues() {
    fSession::delete(self::LAST_POST_SESSION_KEY);
  }
}
