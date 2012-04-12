<?php
// @codeCoverageIgnoreStart
if (!defined('AUTOLOAD_INC')) {
define('AUTOLOAD_INC', 1);

function flourishAutoload($class_name) {
  $file = '../../php/flourish/'.$class_name.'.php';
  if (file_exists($file)) {
    require_once $file;
  }
}
spl_autoload_register('flourishAutoload');

function sutraAutoload($class_name) {
  $file = '../classes/'.$class_name.'.php';
  if (file_exists($file)) {
    require_once $file;
    return;
  }
}
spl_autoload_register('sutraAutoload');
}
// @codeCoverageIgnoreEnd
