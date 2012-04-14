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
  const checkCSRFToken     = 'sRequest::checkCSRFToken';
  const savePostValues     = 'sRequest::savePostValues';
  const setPostValues      = 'sRequest::setPostValues';
  const retrievePostValues = 'sRequest::setPostValues';
  const deletePostValues   = 'sRequest::deletePostValues';

  /**
   * The key in session that holds the last POST values. The last POST values
   *   are only stored when a validation error occurs.
   *
   * @var string
   */
  const LAST_POST_SESSION_KEY_PREFIX = 'sPostRequest::last_post';
  /**
   * Non-throwing check of a CSRF token. Compatible with fValidation as a
   *   callback but the URL must be the same as the current page.
   *
   * @param string $csrf CSRF token string.
   * @param string $url URL to use.
   * @return boolean If the CSRF token is valid for the URL.
   */
  public static function checkCSRFToken($csrf, $url = NULL) {
    try {
      self::validateCSRFToken($csrf, $url);
      return TRUE;
    }
    catch (fValidationException $e) {}
    return FALSE;
  }

  /**
   * Saves the POST values to session.
   *
   * @param string $id Unique ID of the values.
   * @return void
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  public static function savePostValues($id = 'default') {
    $safe_post = array();
    foreach ($_POST as $key => $value) {
      $safe_post[$key] = fRequest::get($key);
    }
    fSession::set(self::LAST_POST_SESSION_KEY_PREFIX.'::'.$id, $safe_post);
  }

  /**
   * Restores the last POST values for a specified ID to $_GET or $_POST.
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
   * Gets the saved POST values for a unique ID.
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
}
