#!/usr/bin/php
<?php
/**
 * This script creates new categories. It must be executed from within a site
 *   root.
 *
 * Example usage in Bash and similar:
 *   ./sutra-create-category.php "My New Category"
 *
 * Exapmle usage in Windows command line (cmd):
 *   php sutra-create-category.php "My New Category"
 *
 * @copyright Copyright (c) 2012 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraScripts
 * @link http://www.example.com/
 *
 * @version 1.0
 */
try {
  $argv[0] = strtolower($argv[0]);
  $php_is_first = substr($argv[0], 0, 3) === 'php' || // plain old 'php' or php.exe
    substr($argv[0], -3) === 'php' ||  // /usr/bin/php or C:\blah\php
    substr($argv[0], -7, 3) === 'php'; // php.exe

  if ($php_is_first && $argc != 3) {
    throw new Exception(sprintf('USAGE: php %s CATEGORY_NAME', $argv[1]));
  }
  else if ($argc != 2) {
    throw new Exception(sprintf('USAGE: %s CATEGORY_NAME', $argv[0]));
  }

  $category_name = $argv[1];

  // Find the site root
  while (!is_file('./global.php')) {
    if (!chdir('..')) {
      throw new Exception('Cannot find site root.');
    }
  }

  require './global.php';

  if (!class_exists('fCore') || !class_exists('sDatabase') || !class_exists('Category')) {
    throw new Exception('Site root contains global.php but is invalid.');
  }

  fCore::enableDebugging(TRUE);
  sDatabase::getInstance();

  $category = new Category;
  $category->setName($category_name);
  $category->setDescription($category_name);
  $category->store();
}
catch (fValidationException $e) {
  print 'Caught fValidationException: '.$e->getMessage()."\n";
  exit(1);
}
catch (fEnvironmentException $e) {
  print 'Caught fEnvironmentException: '.$e->getMessage()."\n";
  exit(1);
}
catch (Exception $e) {
  print $e->getMessage()."\n";
  exit(1);
}
