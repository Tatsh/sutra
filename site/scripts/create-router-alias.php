#!/usr/bin/php
<?php
/**
 * @package SutraScripts
 */
if ($argc != 3) {
  printf('Usage: %s /alias /actual_path_with_leading_/'."\n", $argv[0]);
  exit;
}

$alias = $argv[1];
$path = $argv[2];

chdir('..');
require './global.php';
fCore::enableDebugging(TRUE);
sDatabase::getInstance();

$ra = new RouterAlias;
$ra->setAlias($alias);
$ra->setPath($path);
$ra->store();
