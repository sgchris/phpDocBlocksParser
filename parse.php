<?php

// shortcut
define('DS', DIRECTORY_SEPARATOR);

// define GUI base dir
$baseDir = realpath(__DIR__.DS.'..'.DS.'..'.DS.'module');
echo "base directory: {$baseDir}\n";

// get list of all web api controllers
$webApiControllersList = getWebApiControllerFiles($baseDir);

// parse the files
$parsedData = getParsedControllerFiles($webApiControllersList);

var_dump($parsedData);

////////////////////////////////////////////////////////////////////////

/**
 * get list of web API controller files list
 * @param string $baseDir
 * @return array
 */
function getWebApiControllerFiles($baseDir) {
	// gather files into an array
	$filesList = array();

	// loop the files in the baseDir
	foreach (scandir($baseDir) as $fileName) {

		// skip dot files
		if ($fileName === '.' || $fileName === '..') {
			continue;
		}

		// check if the file is a web API controller
		if (preg_match('/webApi[\d]*Controller\.php/i', $fileName)) {
			$filesList[] = $baseDir.DS.$fileName;
		}

		// dive into the inner folders recursively
		if (is_dir($baseDir.DS.$fileName)) {
			$filesList = array_merge($filesList, getWebApiControllerFiles($baseDir.DS.$fileName));
		}
	}

	return $filesList;
}


/**
 * get list of web API controller classes with their action methods 
 * (format: "public function <name>Action { ... })
 * @param array $controllerFilesList
 * @return array
 */
function getParsedControllerFiles(array $controllerFilesList) {
	global $baseDir;

	// the target array
	$data = array();

	foreach ($controllerFilesList as $controllerFilePath) {
		$parsedFileData = getParsedFile($controllerFilePath);

		// get path relative to GUI base dir
		$relativePath = 'module' . str_ireplace($baseDir, '', $controllerFilePath);

		if ($parsedFileData !== false) {
			$data[$relativePath] = $parsedFileData;
		}
	}

	return $data;
}

/**
 * parse the file to get all its methods with their phpDoc block
 * method is identified by "public function <name>Action"
 * phpDoc block is identified by lines that start with "/*" or "*"
 * 
 * @param string $filePath - full path to file
 * @return array('filePath' => array('methodName' => array(<phpdoc block lines>)))
 */
function getParsedFile($filePath) {
	if (!is_file($filePath) || !is_readable($filePath)) {
		trigger_error("file {$filePath} is not accessible\n", E_USER_WARNING);
		return array();
	}

	// target array
	$parsedData = array();

	// loop file content lines and look for web API action methods
	$contentLines = file($filePath);
	foreach ($contentLines as $lineNumber => $line) {
		if (preg_match('%^\s*public\s+function\s+(.*?)Action%i', $line, $match)) {
			$methodName = $match[1];
			$parsedData[$methodName] = getPhpDocFromFile($contentLines, $lineNumber);
		}
	}

	return $parsedData;
}

/**
 * Parse the PHPDoc block.
 * @param array $fileContent - file content lines
 * @param int $bottomLineNumber - the line number where the PHPDoc ends
 * @return array - lines of the PHPDoc
 */
function getPhpDocFromFile(array $fileContent, $bottomLineNumber) {
	$phpDocBlockLines = array();

	// start from the bottom up
	while ($bottomLineNumber-- > 0) {
		$theLine = trim($fileContent[$bottomLineNumber]);
		if ($theLine[0] == '*' || ($theLine[0] == '/' && $theLine[1] == '*')) {
			$phpDocBlockLines[] = $theLine;
		} else {
			// if the line is not a part of the comment, exit
			break;
		}
	}

	return array_reverse($phpDocBlockLines);
}


/**
 * get php doc lines and transform it into an associative array 
 */
function parsePhpDocLines(array $phpDocLines) {
	foreach ($phpDocBlockLines as $line) {
		$line = preg_replace('%^\s*/?\**\s*%i', '', $line);
	}
}

/// test the reflection API
//$r = new ReflectionMethod('myClass::getSomeString');
//$docs = $r->getDocComment();
//var_dump(get_declared_classes());
//var_dump(get_class_methods('myClass'));
