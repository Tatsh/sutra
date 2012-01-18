<?php
/**
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraSite
 * @link http://www.example.com/
 *
 * @version 1.0
 */
require 'flourish/fLoader.php';
require 'sutra/classes/sLoader.php';
require 'moor/Moor.php';

if (is_readable('./router-overrides.inc')) {
  require './router-overrides.inc';
}

// Initialise the includes and headers.
sLoader::best();

// NOTE Does not get modified upstream
if (is_readable('./includes.inc')) {
  require './includes.inc';
}

/**
 * Get translated version of text if necessary.
 *
 * @todo Implement translations as necessary.
 *
 * @param string $text Text to search for.
 * @param array|string,... $vars Replacement key => value pairs or multiple
 *   string arguments.
 * @return string The text.
 *
 * @package SutraSite
 */
function __($text, $vars = array()) {
  if (!is_array($vars)) {
    $vars = func_get_args();
    array_shift($vars);
  }

  return strtr($text, $vars);
}

/**
 * Get a link, possibly a path alias.
 *
 * This is an alias for sRouter::linkTo().
 *
 * @param string $content Content within the a tag.
 * @param string $path Non-aliased path to link to.
 * @param array Array of attributes for the a tag.
 * @return string HTML tag.
 *
 * @package SutraSite
 *
 * @see sRouter::linkTo()
 */
function l($content, $path, array $attr = array()) {
  return sRouter::linkTo($path, $content, $attr);
}
