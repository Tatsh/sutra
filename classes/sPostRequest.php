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
   * Path prefixes that do not need CSRF checking.
   *
   * @var array
   */
  private static $no_csrf_path_prefixes = array(
    '/json', // may disappear later
  );

  /**
   * Call the processor classes for this URL.
   *
   * @return void
   */
  public static function callProcessorClasses() {
    $url = fURL::get();
    $classes = array();

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
   *
   * @see sPostRequest::callProcessorClasses()
   */
  public static function handle() {
    if (!fRequest::isPost()) {
      return;
    }

    try {
      $url = fURL::get();
      $no_csrf = FALSE;
      foreach (self::$no_csrf_path_prefixes as $prefix) {
        if (substr($url, 0, strlen($prefix)) === $prefix) {
          $no_csrf = TRUE;
          break;
        }
      }

      if ($no_csrf) {
        sRouter::getRoutes();
        Moor::run();
        exit;
      }

      if (fRequest::isAjax()) {
        fJSON::sendHeader();
      }

      fRequest::validateCSRFToken(fRequest::get('csrf', 'string'));
      sRouter::getRoutes();
      Moor::run();
      self::callProcessorClasses();

      fSession::delete(__CLASS__.'::last_post');

      if (!fRequest::isAjax()) {
        $destination = fRequest::get('destination', 'string', '/');
        $destination = fAuthorization::getRequestedURL(TRUE, $destination);
        fURL::redirect($destination);
        return;
      }

      // AJAX request and we are complete
      // If any data needed to be sent it should've been printed in the
      //   callback
      exit;
    }
    catch (fValidationException $e) {
      $error = __(strip_tags($e->getMessage()));
      $error = preg_replace("#(\n)+#", ' ', $error);

      if (strpos($error, 'The form submitted could not be validated as authentic') !== FALSE) {
        $error = __('Unknown error occurred. Please try again.');
      }

      if (!fRequest::isAjax()) {
        // Store all POSTed values in session
        $safe_post = array();
        foreach ($_POST as $key => $value) {
          $safe_post[$key] = fRequest::get($key);
        }
        fSession::set(__CLASS__.'::last_post', $safe_post);

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

        $destination = implode('/', $destination);

        // Add error message to session
        sMessaging::addError($error, $destination);
        fURL::redirect($destination);
        return;
      }

      $ret = array(
        'error' => $error,
        'csrf' => fRequest::generateCSRFToken(Moor::getRequestPath()),
      );

      print fJSON::encode($ret);
      exit;
    }
  }

  /**
   * Restore last POST values so that they may be used in form generation.
   *
   * @return void
   */
  public static function restoreLastPOSTValues() {
    foreach (fSession::get(__CLASS__.'::last_post', array()) as $key => $value) {
      fRequest::set($key, $value);
    }
  }
}
