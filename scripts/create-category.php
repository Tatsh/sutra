#!/usr/bin/php
<?php
/**
 * @package SutraScripts
 */
if ($argc != 2) {
  printf('Usage: %s CategoryName'."\n", $argv[0]);
  exit;
}

$category_name = $argv[1];

chdir('..');
require './global.php';
fCore::enableDebugging(TRUE);
sDatabase::getInstance();

$category = new Category;
$category->setName($category_name);
$category->setDescription($category_name);
$category->store();
