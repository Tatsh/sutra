<?php
/**
 * Logs the user out.
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
class LogoutActionController extends MoorActionController {
  /**
   * Logs the user out and redirects to the front page.
   *
   * @return void
   */
  public function index() {
    fAuthorization::destroyUserInfo();
    fURL::redirect('/');
  }
}
