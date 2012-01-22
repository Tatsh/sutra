#!/usr/bin/php
<?php
/**
 * This script creates new router aliases. It must be executed from within a
 *   site root.
 *
 * Example usage in Bash and similar:
 *   ./sutra-create-router-alias.php /my-special-place /the-actual-path
 *
 * Exapmle usage in Windows command line (cmd):
 *   php sutra-create-router-alias.php /my-special-place /the-actual-path
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
    throw new Exception(sprintf('USAGE: php %s /alias-name /full-path', $argv[1]));
  }
  else if ($argc != 2) {
    throw new Exception(sprintf('USAGE: %s /alias-name /full-path', $argv[0]));
  }

  $alias = $argv[1];
  $path = $argv[2];

  // Find the site root
  while (!is_file('./global.php')) {
    if (!chdir('..')) {
      throw new Exception('Cannot find site root.');
    }
  }

  require './global.php';

  if (!class_exists('fCore') || !class_exists('sDatabase') || !class_exists('RouterAlias')) {
    throw new Exception('Site root contains global.php but is invalid.');
  }

  fCore::enableDebugging(TRUE);
  sDatabase::getInstance();

  $ra = new RouterAlias;
  $ra->setAlias($alias);
  $ra->setPath($path);
  $ra->store();
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
