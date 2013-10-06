<?php
// @codeCoverageIgnoreStart
if (!defined('AUTOLOAD_INC')) {
define('AUTOLOAD_INC', 1);

/**
 * Assuming `flourish` directory in include path.
 */
function flourishAutoload($class_name) {
  $paths = preg_split('/'.preg_quote(PATH_SEPARATOR).'+/', get_include_path());

  foreach ($paths as $path) {
    $file = $path.'/flourish/'.$class_name.'.php';
    if (is_readable($file)) {
      require_once $file;
      break;
    }
  }
}
spl_autoload_register('flourishAutoload');

function sutraAutoload($class_name) {
  $file = '../classes/'.$class_name.'.php';
  if (is_readable($file)) {
    require_once $file;
  }
}
spl_autoload_register('sutraAutoload');
}
// @codeCoverageIgnoreEnd
