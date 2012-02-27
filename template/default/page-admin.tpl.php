<?php
/**
 * Available variables:
 * - $lang
 * - $dir
 * - $head
 * - $is_front
 * - $site_name
 * - $title
 * - $css
 * - $head_js
 * - $sidebar_content
 * - $content
 * - $body_js
 * - $error_messages
 * - $messages
 * - $viewport_content
 * - $user - User object, NULL if not logged in
 * - $logged_in - boolean
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
<!DOCTYPE html>
<html lang="<?php print $lang; ?>" dir="<?php print $dir; ?>">
  <head>
    <?php print $head; ?>

    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no">

    <?php if ($is_front): ?>
      <title><?php print $site_name; ?></title>
    <?php else: ?>
      <title><?php print $title.' | '.$site_name; ?></title>
    <?php endif; ?>
    <?php print $css; ?>
    <?php print $head_js; ?>
    <?php print $conditional_head_js; ?>
  </head>
  <body class="page-admin no-right <?php print $body_class; ?>">
    <h1><?php print $site_name; ?></h1>

    <?php if ($error_message): ?>
      <div id="error-message">
        <?php print $error_message; ?>
      </div>
    <?php elseif ($message): ?>
      <div id="success-message">
        <?php print $message; ?>
      </div>
    <?php endif; ?>

    <div class="layout-978 clear">
      <div id="content-wrapper">
        <div id="content">
          <?php print $content; ?>
        </div>
      </div>
    </div>

    <?php if (!$production_mode): ?>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.js" type="text/javascript"></script>
    <?php else: ?>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js" type="text/javascript"></script>
    <?php endif; ?>
    <?php print $body_js; ?>
  </body>
</html>
