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

foreach (get_declared_classes() as $class_name) {
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
