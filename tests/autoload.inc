<?php
function flourishAutoload($class_name) {
  $file = '../../flourish/'.$class_name.'.php';
  if (file_exists($file)) {
    require_once $file;
  }
}
spl_autoload_register('flourishAutoload');

function sutraAutoload($class_name) {
  $file = '../classes/'.$class_name.'.php';
  if (file_exists($file)) {
    require_once $file;
  }
}
spl_autoload_register('sutraAutoload');