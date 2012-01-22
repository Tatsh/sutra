<?php
/**
 * Available variables:
 * - $csrf
 * - $categories - Array of category names
 * - $textfields - Array of name attribute => attributes
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
<form id="contact-form" action="/contact/post" method="post">
  <header><h2><?php print __('Contact'); ?></h2></header>

  <?php foreach ($textfields as $name => $info): ?>
    <div class="form-textfield-container clear">
      <?php $id = 'edit-'.str_replace('_', '-', $name); ?>
      <label for="<?php print $id; ?>"><?php print $info['label']; ?>
        <?php if (isset($info['required']) && $info['required'] == TRUE): ?>
          <span class="form-required-marker">*</span></label>
          <input id="<?php print $id; ?>" class="form-textfield" type="text" name="<?php print $name; ?>" maxlength="255" required value="<?php print fRequest::encode($name); ?>">
      <?php else: ?>
        </label>
        <input id="<?php print $id; ?>" class="form-textfield" type="text" name="<?php print $name; ?>" maxlength="255" value="<?php print fRequest::encode($name); ?>">
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="form-textarea-container clear">
    <label for="edit-message"><?php print __('Message:'); ?> <span class="form-required-marker">*</span></label>
    <textarea class="form-textarea" id="edit-message" maxlength="1000" name="message" rows="4" cols="0" required><?php print fRequest::encode('message'); ?></textarea>
  </div>

  <input type="hidden" name="csrf" value="<?php print $csrf; ?>">

  <div class="form-ops-container">
    <input type="submit" class="form-submit" value="<?php print __('Submit'); ?>">
  </div>
</form>
