<?php
/**
 * Handles user verifications.
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
class UserVerificationActionController extends MoorActionController {
  public function index() {
    $key = fRequest::get('k', 'string');
    $user_id = fRequest::get('user_id', 'int');

    if (!$key) {
      fURL::redirect('/');
    }

    try {
      $verification = new UserVerification(array('user_id' => $user_id, 'verification_key' => $key));

      if ($verification->getDateUsed()->gt($verification->getDateIssued())) {
        throw new fNotFoundException;
      }

      $verification->setDateUsed(time());
      $verification->store();

      $user = $verification->createUser();
      $user->setVerified(TRUE);
      $user->store();

      sMessaging::add('Your account was successfully verified.', '/login');
    }
    catch (fNotFoundException $e) {
      //die('caught');
    }

    fURL::redirect('/login');
  }
}
