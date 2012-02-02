<?php
/**
 * Available variables:
 * - $csrf - CSRF token for /login/post
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
<form id="login-form" action="/login/post" method="post">
  <div class="form-textfield-container clear">
    <input class="form-textfield" id="edit-name" type="text" name="name" value="<?php print fRequest::encode('name'); ?>" required placeholder="<?php print __('Name or E-mail'); ?>">
  </div>

  <div class="form-textfield-container">
    <input class="form-password form-textfield" id="edit-password" type="password" name="user_password" value="<?php print fRequest::encode('user_password'); ?>" required placeholder="<?php print __('Password'); ?>">
  </div>

  <div class="clear form-checkbox-container">
    <label class="form-checkbox-label" for="edit-session">
      <input type="checkbox" name="session" id="edit-session" value="1" <?php fHTML::showChecked(TRUE, fRequest::get('session', 'bool', FALSE)); ?>>
      <span><?php print __('Remember me'); ?></span>
    </label>
  </div>

  <div class="form-ops-container">
    <input type="submit" class="form-submit ui-button ui-widget ui-state-default ui-corner-all" value="<?php print __('Sign in'); ?>">
  </div>

  <a class="login-forgot-password-link" href="/reset-password"><?php print __('Forgot your password?'); ?></a>

  <input type="hidden" name="csrf" value="<?php print $csrf; ?>">
</form>
