<?php
/**
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraTemplate
 * @link http://www.example.com/
 *
 * @version 1.0
 *
 * $csrf
 * $time_left_text
 * $user - User object
 */
?>
<form id="reset-password-final-form" method="post" action="/reset-password/<?php print $user->encodeUserId(); ?>/post">
  <header><h2>Change Password</h2></header>

  <?php /*<p class="message"><?php print $time_left_text; ?></p>*/ ?>

  <div class="form-textfield-container">
    <label for="edit-password"><?php print __('Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-password" class="form-password form-textfield" type="password" name="user_password" required>
  </div>

  <div class="form-textfield-container">
    <label for="edit-confirm-password"><?php print __('Confirm Password:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-confirm-password" class="form-password form-textfield" type="password" name="user_password2" required>
  </div>

  <input type="hidden" value="<?php print $csrf; ?>" name="csrf">

  <div class="form-ops-container">
    <input type="submit" class="form-submit" value="<?php print __('Change Password'); ?>" name="op">
    <input type="submit" class="form-submit" value="<?php print __('Cancel'); ?>" name="op">
  </div>
</form>
