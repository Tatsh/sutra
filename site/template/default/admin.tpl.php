<?php
/**
 * Administrative page.
 *
 * Available variables:
 * - $items - Array of titles of sections => array of path => title values.
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
<?php foreach ($items as $section => $paths): ?>
  <header><h2><?php print fHTML::encode($section); ?></h2></header>

  <nav>
    <ul>
      <?php foreach ($paths as $path => $title): ?>
        <li>
          <a href="<?php print fHTML::encode($path); ?>">
            <?php print fHTML::encode($title); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

<?php endforeach; ?>