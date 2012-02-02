<?php
/**
 * Available variables:
 * - $csrf - CSRF token string for /account/post
 * - $tabs - Tabs HTML handled in other template
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraTemplate
 * @link http://www.example.com/
 *
 * @version 1.0
 */
?>
<?php print $tabs; ?>
<form id="account-password-form" action="/account/password/post" method="post" class="clear">
  <div class="form-textfield-container clear">
    <label for="edit-current-password"><?php print __('Current Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-current-password" class="form-textfield form-password" type="password" name="current_password" required maxlength="255" autocomplete="off">
  </div>

  <div class="form-textfield-container clear">
    <label for="edit-new-password"><?php print __('New Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-new-password" class="form-textfield form-password" type="password" name="user_password" required maxlength="255" autocomplete="off">
  </div>

  <div class="form-textfield-container clear">
    <label for="edit-new-password2"><?php print __('Confirm New Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-new-password2" class="form-textfield form-password" type="password" name="user_password2" required maxlength="255" autocomplete="off">
  </div>

  <input type="hidden" name="csrf" value="<?php print $csrf; ?>">

  <div class="form-ops-container">
    <input type="submit" class="form-submit ui-button ui-widget ui-state-default ui-corner-all" value="<?php print __('Change Password'); ?>" name="op">
  </div>
</form>
