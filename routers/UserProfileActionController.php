<?php
/**
 * Handles user profile requests.
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
class UserProfileActionController extends MoorActionController {
  /**
   * The main page.
   *
   * @return void
   */
  public function index() {
    $user_id = fRequest::get('user_id', 'int', 0);
    $same_user = FALSE;
    $user = NULL;

    if (fAuthorization::checkLoggedIn()) {
      $current_user = fAuthorization::getUserToken();
      $same_user = $current_user->getUserId() == $user_id;
      $user = $current_user;
    }

    if (!$same_user) {
      try {
        if (!$user_id) {
          throw new fNotFoundException;
        }

        $user = new User($user_id);
      }
      catch (fNotFoundException $e) {
        fURL::redirect('/404');
        return;
      }
    }

    $name = $user->getName();

    sTemplate::render(array(
      'content' => '',
      'title' => $name,
    ));
  }
}
