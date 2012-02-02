<?php
/**
 * Available variables:
 * - $csrf
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
<form id="registration-form" class="not-poll-form" action="/register/post" method="post">
  <div class="form-email-container">
    <label for="edit-email-address"><?php print __('E-mail Address:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-email-address" class="form-email" type="email" name="email_address" value="<?php print fRequest::encode('email_address'); ?>" required autocomplete="off">
  </div>

  <div class="form-textfield-container">
    <label for="edit-name"><?php print __('Name:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-name" class="form-textfield" type="text" name="name" value="<?php print fRequest::encode('name'); ?>" required autocomplete="off" spellcheck="false">
  </div>

  <div class="form-textfield-container">
    <label for="edit-password"><?php print __('Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-password" class="form-password form-textfield" type="password" name="user_password" required autocomplete="off">
  </div>

  <div class="form-textfield-container">
    <label for="edit-confirm-password"><?php print __('Confirm Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-confirm-password" class="form-password form-textfield" type="password" name="user_password2" required autocomplete="off">
  </div>

  <div class="form-ops-container clear">
    <input type="submit" class="form-submit ui-button ui-widget ui-state-default ui-corner-all" value="<?php print __('Register'); ?>">
  </div>

  <input type="hidden" name="csrf" value="<?php print $csrf; ?>">
</form>
