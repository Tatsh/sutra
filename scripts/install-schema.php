#!/usr/bin/php
<?php
chdir(dirname(__FILE__).'/..');
require './global.php';

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
function sutraInstallSchemas() {
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

  require './scripts/install-3rdparty-schema.php';

  foreach ($classes as $class) {
    $schema_sql .= file_get_contents($model_classes_path.$class.'.sql');
  }

  sDatabase::getInstance()->translatedExecute($schema_sql);
}

sutraInstallSchemas();
