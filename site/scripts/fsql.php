#!/usr/bin/php
<?php
/**
 * This script installs schemas.
 *
 * Example usage (in Bash and similar):
 *   ./fsql.php -r /var/www/my-sutra-site -f /home/myname/my-sutra-site/model/SomeModel.sql
 *
 * Exapmle usage in Windows command line (cmd):
 *   php fsql.php -r "C:\xamp\htdocs\my-sutra-site" -f "%HOMEPATH%\my-sutra-site-model/SomeModel.sql"
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
  $short_options = 'r:f:';
  $long_options = array('site-root:', 'sql-file:');
  $options = getopt($short_options, $long_options);

  $root = isset($options['r']) ? $options['r'] : (isset($options['site-root']) ? $options['site-root'] : '');
  if (!$root) {
    throw new Exception('Site root is -r (--site-root/-r) is a required argument.');
  }
  if (!is_readable(realpath($root).DIRECTORY_SEPARATOR.'global.php')) {
    throw new Exception('Site root is invalid.');
  }

  $file = isset($options['f']) ? $options['f'] : (isset($options['sql-file']) ? $options['sql-file'] : '');
  if (!$file) {
    throw new Exception('SQL file (--sql-file/-f) is a required argument.');
  }
  if (!is_readable($file)) {
    throw new Exception('SQL file is invalid.');
  }

  $sql = file_get_contents(realpath($file));

  chdir(realpath($root));
  require './global.php';

  if (!class_exists('fCore') || !class_exists('sDatabase')) {
    throw new Exception('Site root contains global.php but is invalid.');
  }

  fCore::enableDebugging(TRUE);
  sDatabase::getInstance()->translatedExecute($sql);
}
catch (fAuthorizationException $e) {
  print 'Caught fAuthorizationException: '.$e->getMessage()."\n";
  exit(1);
}
catch (fConnectivityException $e) {
  print 'Caught fConnectivityException: '.$e->getMessage()."\n";
  exit(1);
}
catch (fEnvironmentException $e) {
  print 'Caught fEnvironmentException: '.$e->getMessage()."\n";
  exit(1);
}
catch (fProgrammerException $e) {
  print 'Caught fProgrammerException: '.$e->getMessage()."\n";
  print 'Please file an issue with a full log at https://github.com/tatsh/sutra/issues'."\n";
  exit(1);
}
catch (fSQLException $e) {
  print 'Caught fSQLException: '.$e->getMessage()."\n";
  exit(1);
}
catch (Exception $e) {
  print $e->getMessage()."\n";
  exit(1);
}
