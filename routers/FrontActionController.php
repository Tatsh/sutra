<?php
/**
 * Controller for the front page. This class is intended to be replaced by your
 *   own.
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
class FrontActionController extends MoorActionController {
  /**
   * Redirects a user to the front page.
   *
   * @return void
   */
  public function redirectToFront() {
    fURL::redirect('/');
  }

  /**
   * Renders the front page. If the request is via AJAX, will print an empty
   *   JSON array.
   *
   * @return void
   */
  public function index() {
    if (fRequest::isAjax() || fRequest::get('ajax', 'boolean', FALSE)) {
      fJSON::sendHeader();
      print fJSON::encode(array());
      exit;
    }

    $variables = array(
      'title' => __('Home'),
      'content' => 'There is no content!',
    );
    sTemplate::render($variables);
  }
}
