<?php
/**
 * Manages POST requests.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package Sutra
 * @link http://www.sutralib.com/
 *
 * @version 1.2
 */
class sRequest extends fRequest {
  /**
   * The key in session that holds the last POST values. The last POST values
   *   are only stored when a validation error occurs.
   *
   * @var string
   */
  const LAST_POST_SESSION_KEY = 'sPostRequest::last_post';

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
   * Validate a request and redirect to a URL. All POST values are saved
   *   when a validation exception occurs.
   *
   * @param fValidation $validation fValidation instance to try to validate.
   * @param string|callback $redirect_to If not specified, will redirect to
   *   the same URL. Also can specify a callback to call on success.
   * @param string $error_redirect Where to redirect if an error occurs. Also
   *   can specify a callback to call on error.
   * @return void
   * @see sRequest::restoreLastPOSTValues()
   * @see sRequest::deleteLastPOSTValues()
   */
  public static function validatePOST(fValidation $validation, $redirect_to = NULL, $error_redirect = NULL) {
    try {
      $url = fURL::get();

      self::callCallbacks('before');
      $validation->validate();
      self::callCallbacks('after');

      if (is_callable(fCore::callback($redirect_to))) {
        $redirect_to();
      }

      fURL::redirect($redirect_to ? $redirect_to : $url);
    }
    catch (fValidationException $e) {
      self::savePOSTValues();

      if (is_callable(fCore::callback($error_redirect))) {
        $error_redirect($e);
        return;
      }

      fURL::redirect($error_redirect ? $error_redirect : $url);
    }
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

    self::$registered_callbacks[$when][$url][] = $callback;
  }

  /**
   * Calls registered callbacks.
   *
   * @param string $when One of: 'after', 'before'.
   * @return void
   */
  private static function callCallbacks($when) {
    foreach (self::$registered_callbacks[$when]['*'] as $callback) {
      $callback();
    }

    $url = fURL::get();

    if (isset(self::$registered_callbacks[$when][$url])) {
      foreach (self::$registered_callbacks[$when][$url] as $callback) {
        $callback();
      }
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
