<?php
/**
 * Reset password form.
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
<form id="reset-password-form" action="/reset-password/post" method="post">
  <div class="form-textfield-container">
    <label for="edit-name"><?php print __('Name or E-mail Address:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-name" class="form-textfield" type="text" name="email_or_name" value="<?php print fRequest::encode('email_or_name'); ?>" required>
  </div>

  <input type="hidden" value="<?php print fRequest::generateCSRFToken('/reset-password/post'); ?>" name="csrf">

  <div class="form-ops-container">
    <input type="submit" class="form-submit ui-button ui-widget ui-state-default ui-corner-all" value="<?php print __('Reset Password'); ?>">
  </div>
</form>
