<?php

// shortcut
define('DS', DIRECTORY_SEPARATOR);

require_once __DIR__.DS.'libs'.DS.'lib.php';
require_once __DIR__.DS.'libs'.DS.'db.php';

// define GUI base dir
$baseDir = realpath(__DIR__.DS.'..'.DS.'..'.DS.'module');
echo "parsing directory: {$baseDir}\n";

// get list of all web api controllers
$webApiControllersList = getWebApiControllerFiles($baseDir);

// parse the files
$parsedData = getParsedControllerFiles($webApiControllersList);

// insert the parsed data to the DB
$db = DB::getInstance();
foreach ($parsedData as $fileName => $actions) {
	foreach ($actions as $actionName => $docData) {

		// parse the docs
		$parsedPhpDoc = parsePhpDocLines($docData);

		// check if the docData is relevant - has "api" key
		if (isset($parsedPhpDoc['api'])) {
			$operationName = $db->insertDoc($actionName, $fileName, $parsedPhpDoc);
			echo "Action {$actionName} {$operationName}\n";
		}
	}
}

echo "Done!\n";

