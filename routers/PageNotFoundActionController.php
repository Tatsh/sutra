<?php
/**
 * Handles requests to /404.
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
class PageNotFoundActionController extends MoorAbstractController {
  /**
   * Handles requests to /404.
   *
   * @return void
   */
  public function index() {
    header('HTTP/1.0 404 Not Found');

    $text = __('Sorry, the page you are looking for could not be found. Visit the !home.', array('!home' => '<a href="/">homepage</a>'));

    sTemplate::render(array(
      'content' => '<p class="not-found-text">'.$text.'</p>',
      'title' => __('Page Not Found'),
    ));
  }
}
