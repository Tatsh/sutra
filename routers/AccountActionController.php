<?php
/**
 * Manages account pages, starting at /account.
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
class AccountActionController extends MoorActionController {
  /**
   * Manages content at /account.
   *
   * @return void
   */
  public function index() {
    fAuthorization::requireLoggedIn();
    sPostRequest::restoreLastPOSTValues();

    $user = fAuthorization::getUserToken();

    if (fRequest::isAjax()) {
      $data = array(
        'user' => array(
          'name' => $user->getName(),
          'language' => $user->getLanguage(),
          'timezone' => $user->getTimezone(),
        ),
        'csrf' => fRequest::generateCSRFToken('/account/post'),
        'languages' => array('en' => 'English'),
        'timezones' => sTimestamp::getTimezones(),
      );
      print fJSON::encode($data);
      exit;
    }

    $gets = array('name', 'timezone', 'language');
    foreach ($gets as $get) {
      if (fRequest::get($get, 'string', '') === '') {
        $method = 'get'.fGrammar::camelize($get, TRUE);
        fRequest::set($get, $user->$method());
      }
    }

    $content = sTemplate::buffer('account-form', array(
      'user' => $user,
      'languages' => array('en' => __('English')),
      'timezones' => sTimestamp::getTimezones(),
      'csrf' => fRequest::generateCSRFToken('/account/post'),
      'tabs' => sTemplate::buffer('account-tabs', array(
        'tabs' => array(
          '/account' => __('Account'),
          '/account/password' => __('Password'),
        ),
      )),
    ));

    sTemplate::render(array(
      'title' => __('Account'),
      'content' => $content,
    ));
  }

  /**
   * Manages content at /account/password.
   *
   * @return void
   */
  public function password() {
    fAuthorization::requireLoggedIn();
    sPostRequest::restoreLastPOSTValues();

    $user = fAuthorization::getUserToken();

    if (fRequest::isAjax()) {
      $data = array(
        'csrf' => fRequest::generateCSRFToken('/account/post'),
      );
      print fJSON::encode($data);
      exit;
    }

    $content = sTemplate::buffer('account-password-form', array(
      'csrf' => fRequest::generateCSRFToken('/account/password/post'),
      'tabs' => sTemplate::buffer('account-tabs', array(
        'tabs' => array(
          '/account' => __('Account'),
          '/account/password' => __('Password'),
        ),
      )),
    ));

    sTemplate::render(array(
      'title' => __('Password'),
      'content' => $content,
    ));
  }

  /**
   * Manages POST requests for  /account.
   *
   * @return void
   */
  public function post() {
    fAuthorization::requireLoggedIn();

    if (!fRequest::isPost()) {
      if (!fRequest::isAjax()) {
        fURL::redirect('/account');
      }
      else {
        throw new fValidationException('POST requests only.');
      }
    }

    $op = fRequest::get('op');
    if ($op == __('Deactivate My Account')) {
      fURL::redirect('/account/deactivate');
      return;
    }

    $user = fAuthorization::getUserToken();
    $name = $user->getName();
    try {
      $user = fAuthorization::getUserToken();
      $user->populate();
      $user->store();
    }
    catch (fValidationException $e) {
      $user->setName($name);
      $user->store();
      throw $e;
    }

    sMessaging::add(__('User details successfully updated.'), '/account');
    fRequest::set('destination', '/account');
  }

  /**
   * Manages POST requests for /account/password.
   *
   * @return void
   */
  public function passwordPost() {
    fAuthorization::requireLoggedIn();

    if (!fRequest::isPost()) {
      if (!fRequest::isAjax()) {
        fURL::redirect('/account/password');
      }
      else {
        throw new fValidationException('POST requests only.');
      }
    }

    $user = fAuthorization::getUserToken();
    $current = fRequest::get('current_password', 'string', '');
    $new = fRequest::get('user_password', 'string', '');
    $confirm = fRequest::get('user_password2', 'string', '');

    if (!$current || !$new || !$confirm) {
      throw new fValidationException(__('All fields are required.'));
    }

    if (!fCryptography::checkPasswordHash($current, $user->getUserPassword())) {
      throw new fValidationException(__('Incorrect password.'));
    }

    if ($new !== $confirm) {
      throw new fValidationException(__('New passwords must match.'));
    }
    else if (strlen($new) < 8) {
      throw new fValidationException(__('Please use a password with at least 8 characters.'));
    }

    $password = fCryptography::hashPassword($new);
    $user->setUserPassword($password);
    $user->store();

    sMessaging::add(__('Password successfully changed.'), '/account/password');
    fRequest::set('destination', '/account/password');
  }

  /**
   * Manages GET request at /account/deactivate/confirm and asks if user
   *   wishes to deactivate.
   *
   * Administrative accounts will always be blocked from deactivation.
   *
   * @return void
   */
  public function confirmDeactivation() {
    fAuthorization::requireLoggedIn();

    if (fAuthorization::getUserAuthLevel() == 'admin') {
      sMessaging::addError(__('Administrative accounts cannot be deactivated.'), '/account');
      fURL::redirect('/account');
      return;
    }

    $content = sTemplate::buffer('account-deactivate-confirmation', array(
      'message' => __('Are you sure you wish to deactivate your account?'),
      'csrf' => fRequest::generateCSRFToken('/account/deactivate/post'),
    ));

    sTemplate::render(array(
      'title' => __('Deactivate Account Confirmation'),
      'content' => $content,
    ));
  }

  /**
   * Sets account to deactivated state. Blocks admin accounts.
   *
   * @todo All the site variables used here must have a place past /admin
   *   for customisation.
   *
   * @return void
   */
  public function deactivateAccount() {
    fAuthorization::requireLoggedIn();

    if (!fRequest::isPost()) {
      sMessaging::addError(__('An unknown error occurred.'), '/account');
      fURL::redirect('/account');
      return;
    }

    if (fRequest::get('op', 'string', __('No')) == __('Yes')) {
      $user = fAuthorization::getUserToken();
      $user->setDeactivated(time());
      $user->store();

      // Log out
      fAuthorization::destroyUserInfo();

      $link = fURL::getDomain().'/login';
      $from_email = SiteVariable::getValue('deactivate_account_from_email', 'string', 'admin@somewhere.com');
      $subject = __(SiteVariable::getValue('deactivate_account_email_subject', 'string', __('Account deactivated')));

      // Keep the lines broken up
      $default_body = "
      Sorry to see you go.

      Your account can be re-activated at any time by logging in and confirming to re-activate.

      !link";
      $body = SiteVariable::getValue('deactivate_account_email_body', NULL, $default_body);
      $body = __($body, array('!link' => $link));

      // Send e-mail
      /** From email problem with ssmtp and Gmail
       * http://flourishlib.com/discussion/1/580
       */
      $email = new fEmail;
      $email->setFromEmail($from_email);
      $email->setReplyToEmail($from_email);
      $email->setSenderEmail($from_email);
      $email->setSubject($subject);
      $email->addRecipient($user->getEmailAddress());
      $email->setBody($body);
      $email->send();

      sMessaging::add(__('Successfully deactivated your account.'), '/login');
      fRequest::set('destination', '/login');
    }
    else {
      fRequest::set('destination', '/account');
    }
  }

  /**
   * Confirms reactivation of acccount.
   *
   * @return void
   */
  public function confirmReactivation() {
    $user_id = fSession::get('reactivate_user_id', FALSE);
    if (fAuthorization::checkLoggedIn() || !$user_id) {
      fURL::redirect('/');
      return;
    }

    $content = sTemplate::buffer('account-reactivate-confirmation', array(
      'message' => __('Do you wish to reactivate your account?'),
      'csrf' => fRequest::generateCSRFToken('/account/reactivate/post'),
    ));

    sTemplate::render(array(
      'title' => __('Reactivate Account Confirmation'),
      'content' => $content,
    ));
  }

  /**
   * Reactivates account.
   *
   * @return void
   */
  public function reactivateAccount() {
    $user_id = fSession::get('reactivate_user_id', FALSE);
    fSession::delete('reactivate_user_id');

    if (fAuthorization::checkLoggedIn() || !$user_id) {
      fURL::redirect('/');
      return;
    }

    $op = fRequest::get('op', 'string', __('No'));

    if ($op == __('Yes')) {
      try {
        $user = new User($user_id);
        $user->setDeactivated(0);
        $user->store();

        sMessaging::add(__('Successfully re-activated your account.'), '/');

        fAuthorization::setUserToken($user);
        fAuthorization::setUserAuthLevel($user->getAuthLevel());
        if (fRequest::get('session', 'bool', FALSE)) {
          fSession::enablePersistence();
        }

        fRequest::set('destination', '/');
        return;
      }
      catch (fNotFoundException $e) {}
      catch (fValidationException $e) {}

      sMessaging::addError(__('An unknown error occurred.'));
      fRequest::set('destination', '/');

      return;
    }

    fRequest::set('destination', '/');
  }
}
