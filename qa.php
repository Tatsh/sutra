<?php
require 'flourish/fLoader.php';
require 'classes/sLoader.php';
sLoader::best();

$exceptions = array(
  'sImage' => array('determineMimeType'),
  'sNumber' => array('ordinalNumberPrefixedCallback'),
  'sORMJSON' => array('JSONToValue', 'valueToJSON'),
  'sProcessException' => array('registerCallback'),
);
$classes = get_declared_classes();

foreach ($classes as $class_name) {
  $reflect = new ReflectionClass($class_name);
  $methods = $reflect->getMethods(ReflectionMethod::IS_STATIC);

  foreach ($methods as $method) {
    if (isset($exceptions[$class_name]) && in_array($method->name, $exceptions[$class_name])) {
      continue;
    }

    if ($class_name[0] == 's' && $method->isPublic() && !$reflect->hasConstant($method->name)) {
      print $class_name.' lacks constant: '.$method->name."\n";
    }
  }
}
print "\n";

$exceptions = array(
  'fORMValidation' => TRUE,
);

foreach ($classes as $class_name) {
  if (isset($exceptions[$class_name])) {
    continue;
  }

  $reflect = new ReflectionClass($class_name);

  if ($reflect->isInternal()) {
    continue;
  }

  $methods = $reflect->getMethods();
  $all_static = TRUE;

  foreach ($methods as $method) {
    if (!$method->isStatic() && $method->name !== '__construct') {
      $all_static = FALSE;
    }
  }

  if ($all_static) {
    try {
      $method = $reflect->getMethod('__construct');
      if (!$method->isPrivate()) {
        print 'Static class '.$class_name.'\'s __construct() method must be private.'."\n";
      }
    }
    catch (Exception $e) {
      print $class_name.' has only static methods and lacks a private __construct() method.'."\n";
    }
  }
}
