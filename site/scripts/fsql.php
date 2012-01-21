#!/usr/bin/php
<?php
/**
 * @package SutraScripts
 */
if ($argc != 2) {
  printf('Usage: %s FILE'."\n", $argv[0]);
  exit;
}

$file = $argv[1];
$sql = '';
if (is_readable($file)) {
  $sql = file_get_contents($file);
}

chdir('..');
require './global.php';
fCore::enableDebugging(TRUE);
sDatabase::getInstance()->translatedExecute($sql);
