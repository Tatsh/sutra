<?php
/**
 * Handler for AJAX requests for CSRF tokens.
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
class CSRFTokenController extends MoorActionController {
  /**
   * Handler for AJAX requests for CSRF tokens.
   *
   * @return void
   */
  public function ajax() {
    if (!fRequest::isAjax()) {
      fURL::redirect('/');
      return;
    }

    fJSON::sendHeader();

    $path = fRequest::get('path', 'string', '/');
    if (!$path) {
      print fJSON::encode(array('error' => 'No path.'));
    }

    print fJSON::encode(array('csrf' => fRequest::generateCSRFToken($path), 'path' => $path));
    exit;
  }
}
