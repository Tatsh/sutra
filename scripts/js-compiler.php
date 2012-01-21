#!/usr/bin/php
<?php
/**
 * Compiles JavaScript files.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license Proprietary.
 *
 * @package PoluzaScripts
 * @link http://www.example.com/
 *
 * @version 1.0
 */

$php_is_first = substr(strtolower($argv[0]), 0, 3) === 'php';

if ($php_is_first && $argc != 3) {
  printf('USAGE: php %s SITE_ROOT'."\n", $argv[1]);
  exit(1);
}
else if ($argc != 2) {
  printf('USAGE: %s SITE_ROOT'."\n", $argv[0]);
  exit(1);
}

$root = $php_is_first ? realpath($argv[2]) : realpath($argv[1]);
if (!is_readable($root.'/global.php')) {
  print 'Not a valid root directory.'."\n";
  exit(1);
}

try {
  chdir($root);
  require './global.php';

  sDatabase::getInstance();

  require dirname(__FILE__).'/compile-javascript.inc';

  print compileJavaScript();
}
catch (fUnexpectedException $e) {
  print $e->getMessage()."\n";
  exit(1);
}
