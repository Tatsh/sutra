<?php
/**
 * Controller for the front page.
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
   * Renders the front page. Will call any classes that implement the
   *   sAJAXResponder interface with the URL /.
   *
   * @return void
   */
  public function index() {
    if (fRequest::isAjax() || fRequest::get('ajax', 'boolean', FALSE)) {
      fJSON::sendHeader();
      $args = array('/', fRequest::isGet() ? 'GET' : 'POST');
      $data = array();

      foreach (get_declared_classes() as $class) {
        $reflect = new ReflectionClass($class);
        if ($reflect->implementsInterface('sAJAXResponder')) {
          $data = array_merge($data, fCore::call($class.'::requestAt', $args));
        }
      }

      print fJSON::encode($data);
      exit;
    }

    $config = sConfiguration::getInstance();
    $variables = array(
      'title' => __('Home'),
      'content' => 'There is no content!',
    );
    sTemplate::render($variables);
  }
}
