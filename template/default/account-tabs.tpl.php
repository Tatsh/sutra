<?php
/**
 * Available variables:
 * - $tabs - List of URL => Tab name array
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
<nav class="account-tabs-container">
  <ul class="account-tabs-list">
    <?php foreach ($tabs as $path => $tab): ?>
      <li>
        <?php if (fURL::get() == $path): ?>
          <a class="active" href="<?php print fHTML::encode($path); ?>"><?php print fHTML::encode($tab); ?></a>
        <?php else: ?>
          <a href="<?php print fHTML::encode($path); ?>"><?php print fHTML::encode($tab); ?></a>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
