<?php
/**
 * Handles user registration.
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
class RegisterActionController extends MoorActionController {
  /**
   * Handles /register GET request.
   *
   * Also handles AJAX requests to ensure user data is unique during
   *   registration.
   *
   * @return void
   */
  public function index() {
    if (fAuthorization::checkLoggedIn()) {
      fURL::redirect('/');
      return;
    }

    if (fRequest::isAjax() && fRequest::get('check', 'bool', FALSE)) {
      fJSON::sendHeader();
      $ret = array();
      $field = fRequest::getValid('field', array('INVALID', 'email_address', 'name'));
      $value = fRequest::get('value', 'string', '');

      if ($value !== '') {
        switch ($field) {
          case 'email_address':
            try {
              new User(array('email_address' => $value));
              $ret['error'] = __('This e-mail address has already been registered.');
            }
            catch (fNotFoundException $e) {
              $ret['message'] = __('Good.');
            }
            break;

          case 'name':
            try {
              new User(array('name' => $value));
              $ret['error'] = __('This name has already been registered.');
            }
            catch (fNotFoundException $e) {
              $ret['message'] = __('Good.');
            }
            break;

          default:
            $ret['error'] = __('Invalid field.');
            break;
        }
      }

      print fJSON::encode($ret);
      return;
    }

    sPostRequest::restoreLastPOSTValues();

    $variables = array(
      'csrf' => fRequest::generateCSRFToken('/register/post'),
    );

    $content = sTemplate::buffer('registration-form', $variables);

    $variables = array(
      'title' => 'Registration',
      'content' => $content,
    );
    sTemplate::render($variables);
  }

  /**
   * Handles POST requests.
   *
   * @throws fValidationException If e-mail address is invalid; if the display
   *   name is an e-mail address; if the passwords do not match; if the
   *   password is not at least 8 characters long.
   *
   * @return void
   */
  public function post() {
    if (!fRequest::isPost()) {
      fURL::redirect('/register');
    }

    $config = sConfiguration::getInstance();
    $name = fRequest::get('name', 'string');
    $email_address = fRequest::get('email_address', 'string');
    $password = fRequest::get('user_password', 'string');
    $destination = fRequest::get('destination', 'string', '/login');

    if (preg_match(fEmail::EMAIL_REGEX, $name)) {
      throw new fValidationException(__('Display name cannot be an e-mail address.'));
    }

    if ($password != fRequest::get('user_password2', 'string')) {
      throw new fValidationException(__('Passwords must match.'));
    }
    else if (strlen($password) < 8) {
      throw new fValidationException(__('Please use a password that is at least 8 characters long.'));
    }

    $allowed = $config->getAllowedEmailDomains();
    if (!is_array($allowed)) {
      $allowed = array();
    }
    $matches = array();
    if (!preg_match(fEmail::EMAIL_REGEX, $email_address, $matches)) {
      throw new fValidationException(__('Invalid e-mail address.'));
    }
    if (count($allowed) && isset($matches[2]) && !in_array($matches[2], $allowed) || !isset($matches[2])) {
      throw new fValidationException(__('Invalid e-mail address.'));
    }

    $user = new User;
    $user->populate();
    $user->store();

    $user_id = $user->getUserId();
    $cwd = getcwd();
    fSession::set(__CLASS__.'::'.$cwd.'::last_registered_user_id', $user_id);

    // User is not verified
    $verification = new UserVerification;
    $verification->setUserId($user_id);
    $key = fCryptography::randomString(64);
    $verification->setVerificationKey($key);
    $verification->store();

    // Send verification email
    $email = new fEmail;
    $email->setFromEmail('admin@poluza.com');
    $email->setSubject(__('Verifiy your account at !name', array('!name' => $config->getSiteName())));
    $email->addRecipient($email_address);
    $link = fURL::getDomain().'/user/verify/'.$user_id.'?k='.urlencode($key);
    $email->setBody("
    Verify your account by following the URL:

    $link
");
    $email->send();

    sMessaging::add(__('Registered successfully. You should receive an e-mail in a few moments with a verification link to click and then you will be able to login.'), $destination);

    fRequest::set('destination', $destination);
  }
}
