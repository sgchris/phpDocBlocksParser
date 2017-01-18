<?php

// shortcut
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'lib.php';

// define GUI base dir
$baseDir = realpath(__DIR__.DS.'..'.DS.'..'.DS.'module');
echo "base directory: {$baseDir}\n";

// get list of all web api controllers
$webApiControllersList = getWebApiControllerFiles($baseDir);

// parse the files
$parsedData = getParsedControllerFiles($webApiControllersList);

var_dump($parsedData);

////////////////////////////////////////////////////////////////////////
/// test the reflection API
//$r = new ReflectionMethod('myClass::getSomeString');
//$docs = $r->getDocComment();
//var_dump(get_declared_classes());
//var_dump(get_class_methods('myClass'));
