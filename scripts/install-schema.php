#!/usr/bin/php
<?php
/**
 * Install the schemas in the correct order. Add your own class names to the
 *   $third_party_model_classes array in install-3rdparty-schema.php.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraScripts
 * @link http://www.example.com/
 *
 * @version 1.0
 */

try {
  // Find the site root
  while (!is_file('./global.php')) {
    if (!chdir('..')) {
      throw new Exception('Cannot find site root.');
    }
  }

  require './global.php';

  if (!class_exists('fCore') || !class_exists('sDatabase')) {
    throw new Exception('Site root contains global.php but is invalid.');
  }

  $classes = array(
    'User',
    'Category',
    'CompiledJavascriptFile',
    'ContactMailMessage',
    'ResetPasswordRequest',
    'RouterAlias',
    'SiteVariable',
    'UserVerification',
  );
  $schema_sql = '';
  $model_classes_path = sLoader::getModelClassesPath();

  foreach ($classes as $class) {
    $schema_sql .= file_get_contents($model_classes_path.$class.'.sql');
  }

  fCore::enableDebugging(TRUE);
  sDatabase::getInstance()->translatedExecute($schema_sql);
}
catch (fSQLException $e) {
  print 'Caught fSQLException: '.strip_tags($e->getMessage())."\n";
  exit(1);
}
catch (Exception $e) {
  print $e->getMessage();
  exit(1);
}
