<?php
/**
 * Available variables:
 * - $user - User object
 * - $languages - ISO => Full name list of languages
 * - $timezones - Offset value => Timezone name
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
<form id="account-form" action="/account/post" method="post" class="clear">
  <div class="form-textfield-container clear">
    <label for="edit-name"><?php print __('User name:'); ?> <span class="form-required-marker">*</span></label>
    <input id="edit-name" class="form-textfield" type="text" name="name" value="<?php print fRequest::encode('name'); ?>" required maxlength="128">
  </div>

  <div class="form-select-container clear">
    <label for="edit-language"><?php print __('Language:') ?> <span class="form-required-marker">*</span></label>
    <select class="form-select" id="edit-language" name="language">
      <?php $lang = fRequest::get('language'); ?>
      <?php foreach ($languages as $id => $name): ?>
        <?php if ($lang == $id): ?>
          <option value="<?php print fHTML::encode($id); ?>" selected><?php print fHTML::encode($name); ?></option>
        <?php else: ?>
          <option value="<?php print fHTML::encode($id); ?>"><?php print fHTML::encode($name); ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
  </div>

  <?php /*
  <div class="form-select-container">
    <label for="edit-timezone"><?php print __('Timezone:') ?> <span class="form-required-marker">*</span></label>
    <select class="form-select" id="edit-timezone" name="timezone">
      <?php $tz = fRequest::get('timezone'); ?>
      <?php foreach ($timezones as $offset => $zone): ?>
        <?php if ($zone == $tz): ?>
          <option value="<?php print fHTML::encode($offset); ?>" selected>
            <?php print fHTML::encode('(GMT '.sTimestamp::formatTimezoneWithNumber($offset).') '.str_replace('_', ' ', $zone)); ?>
          </option>
        <?php else: ?>
          <option value="<?php print fHTML::encode($offset); ?>">
            <?php print fHTML::encode('(GMT '.sTimestamp::formatTimezoneWithNumber($offset).') '.str_replace('_', ' ', $zone)); ?>
          </option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
  </div>*/?>

  <input type="hidden" name="csrf" value="<?php print $csrf; ?>">

  <div class="form-ops-container">
    <input type="submit" class="form-submit ui-button ui-widget ui-state-default ui-corner-all" value="<?php print __('Save'); ?>" name="op">
    <input type="submit" class="form-submit ui-button ui-widget ui-state-default ui-corner-all" value="<?php print __('Deactivate My Account'); ?>" name="op">
  </div>
</form>
