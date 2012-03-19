<?php
/**
 * Handles reset password requests.
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
class ResetPasswordActionController extends MoorActionController {
  public function index() {
    if (fAuthorization::checkLoggedIn()) {
      fURL::redirect('/');
      return;
    }

    $content = sTemplate::buffer('reset-password-form', array());
    sTemplate::render(array('title' => __('Reset Password'), 'content' => $content));
  }

  public function submitted() {
    if (fAuthorization::checkLoggedIn()) {
      fSession::delete('has_reset_password_request');
      fURL::redirect('/');
      return;
    }

    if (fSession::get('has_reset_password_request', FALSE)) {
      fSession::delete('has_reset_password_request');

      $content = sTemplate::buffer('reset-password-submitted', array());
      sTemplate::render(array('title' => __('Reset Password'), 'content' => $content));
    }
    else {
      fURL::redirect('/');
    }
  }

  /**
   * Create a request to reset a password in the database.
   * Will redirect to /reset-password/:user_id/submitted which is handled by
   *   ResetPasswordActionController->submitted().
   *
   * @todo Make body customisable.
   * @todo Test if we can use __() with fEmail->setBody().
   * @todo HTML body with fEmail.
   */
  private function createResetPasswordRequest(User $user) {
    $user_id = $user->getUserId();
    $site_name = sConfiguration::getInstance()->getSiteName();
    $key = fCryptography::randomString(64);

    $request = new ResetPasswordRequest;
    $request->setUserId($user->getUserId());
    $request->setRequestKey($key);
    $request->store();

    // For the submitted callback
    fSession::set('has_reset_password_request', TRUE);

    // Send the e-mail
    try {
      $domain = fURL::getDomain();
      $link = $domain.'/reset-password/'.$user_id.'?k='.urlencode($key);

      $subject = 'Reset password request at !site_name';
      $subject = SiteVariable::getValue(__CLASS__.'::subject', 'string', $subject);

      $email = new fEmail;
      $email->addRecipient($user->getEmailAddress());
      $email->setSubject(__($subject, array('!site_name' => $site_name)));
      $email->setBody("A request was made to reset the password associated with this e-mail address. If you did not make this request, simply ignore this e-mail.

  If you did make this request, follow the URL below to reset your password. This reset password request will expire in approximately 30 minutes.

  $link

  --
  $site_name
");

      $domain = str_replace(array('http://', 'https://'), '', $domain);
      $default_email = 'support@'.$domain;
      $webmaster_email = SiteVariable::getValue('sConfiguration::webmaster_email', 'string', $default_email);
      $email->setFromEmail($webmaster_email);
      $email->send();
    }
    catch (fConnectivityException $e) {
      throw new fValidationException('An error occurred while attempting to send the e-mail. Please try again.');
    }

    fURL::redirect('/reset-password/submitted');
  }

  public function post() {
    if (!fRequest::isPost()) {
      fURL::redirect('/');
      return;
    }

    $email_or_name = fRequest::get('email_or_name', 'string');

    try {
      $user = new User(array('name' => $email_or_name));
      $this->createResetPasswordRequest($user);
      return;
    }
    catch (fNotFoundException $e) {}

    try {
      $user = new User(array('email_address' => $email_or_name));
      $this->createResetPasswordRequest($user);
      return;
    }
    catch (fNotFoundException $e) {}

    throw new fValidationException(__('Unknown user name or e-mail address.'));
  }

  /**
   * Final reset form which will normally be arrived at by URL in e-mail.
   *
   * @return void
   */
  public function resetForm() {
    if (fAuthorization::checkLoggedIn()) {
      fURL::redirect('/');
      return;
    }

    $user_id = fRequest::get('user_id', 'int', 0);
    $key = fRequest::get('k', 'string', NULL);
    $time = time();

    if (!$user_id || !$key) {
      fURL::redirect('/');
      return;
    }

    try {
      $request = new ResetPasswordRequest(array('user_id' => $user_id, 'request_key' => $key));
      if ($request->getUsed()) {
        throw new fNotFoundException;
      }

      $user = new User($user_id);

      if ($request->getDateCreated()->gt(time() + (30 * 60))) {
        throw new fNotFoundException;
      }

      $minutes = ($request->getDateCreated()->format('U') - $time) + 30;

      $content = sTemplate::buffer('reset-password-final-form', array(
        'user' => $user,
        'time_left_text' => __('This form will expire in !n minutes.', array('!n' => $minutes)),
        'csrf' => fRequest::generateCSRFToken('/reset-password/'.$user_id.'/post'),
      ));
      fSession::set('resetting_password_for_user_id', $user_id);
      fSession::set('resetting_password_for_key', $key);
      sTemplate::render(array('content' => $content, 'title' => __('Reset Password')));
    }
    catch (fNotFoundException $e) {
      fURL::redirect('/');
    }
  }

  public function resetFormPost() {
    $user_id = fSession::get('resetting_password_for_user_id', FALSE);
    $key = fSession::get('resetting_password_for_key', FALSE);
    $op = fRequest::get('op', 'string');

    if (!fRequest::isPost() || !$user_id || !$key) {
      fURL::redirect('/');
      return;
    }

    if ($op == __('Cancel')) {
      fSession::delete('resetting_password_for_key');
      fSession::delete('resetting_password_for_user_id');
      fURL::redirect('/');
      return;
    }

    $password = fRequest::get('user_password', 'string');
    if ($password !== fRequest::get('user_password2', 'string')) {
      throw new fValidationException(__('Passwords must match.'));
    }
    if (strlen($password) < 8) {
      throw new fValidationException(__('Please enter a password with at least 8 characters.'));
    }

    try {
      $request = new ResetPasswordRequest(array('user_id' => $user_id, 'request_key' => $key));
      if ((bool)$request->getUsed()) {
        throw new fNotFoundException;
      }

      $user = new User($user_id);
      $password = fCryptography::hashPassword($password);
      $user->setUserPassword($password);
      $user->store();

      $request->setUsed(TRUE);
      $request->store();

      fSession::delete('resetting_password_for_key');
      fSession::delete('resetting_password_for_user_id');

      sMessaging::add(__('Your password was successfully changed. You may now log in with your new credentials.'), 'login');
      fURL::redirect('/login');
    }
    catch (fNotFoundException $e) {
      throw new fValidationException(__('User not found.'));
    }
  }
}
