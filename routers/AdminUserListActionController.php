<?php
/**
 * Manages users at /admin/users
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
class AdminUserListActionController extends MoorActionController {
  public function index() {
    sAuthorization::requireAdministratorPrivileges();

    $sort = fCRUD::getSortColumn(array('name', 'date_created'));
    $direction = fCRUD::getSortDirection('desc');
    fCRUD::redirectWithLoadedValues();

    $content = sTemplate::buffer('admin-user-list', array(
      'users' => fRecordSet::build('User', array('name!=' => 'guest'), array($sort => $direction)),
      'user_id' => fRequest::get('user_id'),
    ));

    sTemplate::render(array(
      'title' => __('Users'),
      'content' => $content,
    ));
  }
}
