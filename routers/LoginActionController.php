<?php
/**
 * Controller for the login page.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraRouters
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class LoginActionController extends MoorActionController {
  /**
   * Handles POST requests to login.
   *
   * If a user gets their password wrong 5 times, the session is locked from
   *   logging in for 10 minutes.
   *
   * @return void
   */
  public function post() {
    if (!fRequest::isPost()) {
      fURL::redirect('/login');
      return;
    }

    try {
      $time = time();
      if (fSession::get(__CLASS__.'::account_locked', FALSE)) {
        $end_time = fSession::get(__CLASS__.'::account_locked_till');
        if ($end_time < $time) {
          // No longer locked
          fSession::delete(__CLASS__.'::account_locked');
          fSession::delete(__CLASS__.'::account_locked_till');
        }
        else {
          throw new fValidationException(__('This account has been locked. Please wait 10 minutes and try again.'));
        }
      }

      $name_or_email = fRequest::get('name', 'string');
      try {
        $user = new User(array('name' => $name_or_email));
      }
      catch (fNotFoundException $e) {
        $user = new User(array('email_address' => $name_or_email));
      }

      $pass = fRequest::get('user_password', 'string');
      $uses_old = FALSE;
      $wrong = !fCryptography::checkPasswordHash($pass, $user->getUserPassword());
      if ($wrong && class_exists('PasswordHash')) {
        // Try the old one
        $hasher = new PasswordHash(8, TRUE);
        $uses_old = $hasher->CheckPassword($pass, $user->getUserPassword());

        if (!$uses_old) {
          throw new fValidationException(__('User name or password incorrect.'));
        }
      }
      else if ($wrong) {
        $attempts = fSession::get(__CLASS__.'::login_attempts', 0);
        $attempts++;
        fSession::set(__CLASS__.'::login_attempts', $attempts);

        if ($attempts >= 5) {
          fSession::set(__CLASS__.'::account_locked', TRUE);
          fSession::set(__CLASS__.'::account_locked_till', $time + 600);
          throw new fValidationException(__('This account has been locked for 10 minutes.'));
        }

        throw new fValidationException(__('User name or password incorrect.'));
      }

      // Update the user password to the new scheme
      if ($uses_old) {
        $hash = fCryptography::hashPassword($pass);
        $user->setUserPassword($hash);
      }

      if (!$user->isVerified()) {
        throw new fValidationException(__('You must verify your account. Check your e-mail for a verification link.'));
      }

      // Any timestamp set to 00:00 will be negative or 0
      if (!$user->isActivated()) {
        fSession::set('reactivate_user_id', $user->getUserId());
        fURL::redirect('/account/reactivate');
        return;
      }

      $user->store(); // Store so that last_access gets updated
    }
    catch (fNotFoundException $e) {
      throw new fValidationException(__('User name or password incorrect.'));
    }

    // Log in
    fAuthorization::setUserAuthLevel($user->getAuthLevel());
    fAuthorization::setUserToken($user);
    if (fRequest::get('session', 'bool', FALSE)) {
      fSession::enablePersistence();
    }
  }

  /**
   * Handles requests for the log in form.
   *
   * @return void
   */
  public function index() {
    if (fAuthorization::checkLoggedIn()) {
      fURL::redirect('/');
      return;
    }

    $variables = array(
      'csrf' => fRequest::generateCSRFToken('/login/post'),
    );
    $content = sTemplate::buffer('login-form', $variables);

    $variables = array(
      'title' => __('Sign in'),
      'content' => $content,
    );
    sTemplate::render($variables);
  }
}
