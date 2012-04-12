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
  const LAST_POST_SESSION_KEY_PREFIX = 'sPostRequest::last_post';

  /**
   * Callback to validate a CSRF token. For use with fValidation.
   *
   * @param string $csrf CSRF token to validate.
   * @return boolean If the CSRF token is valid.
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public static function validateCSRFTokenCallback($csrf) {
    try {
      self::validateCSRFToken($csrf);
      return TRUE;
    }
    catch (fValidationException $e) {}
    return FALSE;
  }

  /**
   * Saves the POST values to sessions.
   *
   * @param string $id Unique ID of the values.
   * @return void
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public static function savePostValues($id = 'main') {
    $safe_post = array();
    foreach ($_POST as $key => $value) {
      $safe_post[$key] = fRequest::get($key);
    }
    fSession::set(self::LAST_POST_SESSION_KEY_PREFIX.'::'.$id, $safe_post);
  }

  /**
   * Restores the last POST values for a specified ID to $_GET, $_POST, or
   *   $_REQUEST.
   *
   * @param string $id Unique ID of the values.
   * @return void
   */
  public static function setPostValues($id = 'default') {
    $values = fSession::get(self::LAST_POST_SESSION_KEY_PREFIX.'::'.$id, array());
    foreach ($values as $key => $value) {
      fRequest::set($key, $value);
    }
  }

  /**
   * Get the saved POST values for a unique ID.
   *
   * @param string $id Unique ID of the values.
   * @return array Array of values. Can return an empty array.
   */
  public static function retrievePostValues($id = 'default') {
    $value = fSession::get(self::LAST_POST_SESSION_KEY_PREFIX.'::'.$id, array());
    if (!is_array($value)) {
      $value = array();
    }
    return $value;
  }

  /**
   * Deletes all POST values stored for a particular ID.
   *
   * @param string $id Unique ID of the values.
   * @return void
   */
  public static function deletePostValues($id = 'default') {
    fSession::delete(self::LAST_POST_SESSION_KEY_PREFIX.'::'.$id);
  }

  /**
   * Restore last POST values so that they may be used in form generation.
   *
   * @param string $id Unique ID of the values.
   * @return void
   * @deprecated
   */
  public static function restoreLastPOSTValues($id = 'default') {
    self::setPostValues($id);
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
   * @deprecated
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
   * @deprecated
   */
  private static function callCallbacks($when) {
    foreach (self::$registered_callbacks[$when]['*'] as $callback) {
      fCore::call($callback);
    }

    $url = fURL::get();

    if (isset(self::$registered_callbacks[$when][$url])) {
      foreach (self::$registered_callbacks[$when][$url] as $callback) {
        fCore::call($callback);
      }
    }
  }

  /**
   * Validates a CSRF token in the 'csrf' POST/GET/PUT parameter.
   *
   * @internal
   *
   * @param string $url URL to validate with.
   * @return void
   * @deprecated
   */
  public static function validateToken($url = NULL) {
    $token = self::get('csrf', 'string');
    self::validateCSRFToken($token, $url);
  }

  /**
   * Registered callbacks.
   *
   * @var array
   * @deprecated
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
   * @deprecated
   */
  public static function validatePost(fValidation $validation, $redirect_to = NULL, $error_redirect = NULL) {
    try {
      $url = fURL::get();

      if (!$error_redirect) {
        $error_redirect = $url;
      }

      self::callCallbacks('before');
      $validation->validate();
      self::callCallbacks('after');

      $cb = fCore::callback($redirect_to);
      if (is_callable($cb)) {
        $cb();
      }

      fURL::redirect($redirect_to ? $redirect_to : $url);
    }
    catch (fValidationException $e) {
      self::savePOSTValues();

      $cb = fCore::callback($error_redirect);
      if (is_callable($cb)) {
        $cb($e);
        return;
      }

      $message = strip_tags($message);
      $message = str_replace("\n", ' ', $message);

      fMessaging::create('validation', $error_redirect, $message);
      fURL::redirect($error_redirect);
    }
  }
}
