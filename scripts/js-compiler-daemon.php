#!/usr/bin/php
<?php
/**
 * Waits and compiles JavaScript files.
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

chdir($root);
require './global.php';

sDatabase::getInstance();

require dirname(__FILE__).'/compile-javascript.inc';

for (;;) {
  try {
    $records = fRecordSet::build('CompiledJavascriptFile', array('completed=' => FALSE), array('date_created' => 'desc'), 1);
    $records->tossIfEmpty();

    $record = $records->getRecord(0);
    $filename = $record->getFilename();
    $compiled = compileJavaScript();

    $ret = file_put_contents('./'.$record->getFilename(), $compiled, LOCK_EX);
    if ($ret === FALSE) {
      throw new fUnexpectedException('Could not write to file %s', $record->getFilename());
    }

    $record->setCompleted(TRUE);
    $record->store();
  }
  catch (fUnexpectedException $e) {
    $message = strip_tags($e->getMessage());
    $message = preg_replace("#\n+#", ' ', $message);
    fCore::debug($message."\n");
  }
  catch (fEmptySetException $e) {
    //fCore::debug('Nothing to compile.');
  }

  //fCore::debug('Sleeping for 30 seconds.');
  sleep(30);
}
