<?php

require_once __DIR__.'/base.php';
require_once __DIR__.'/../lib.php';

$docBlock = [
	'/**',
	' * this is some explanation',
	' *',
	' * @api',
	' * @version 1.14',
	' * @param string $s',
	' * @return string',
	' */',
];
$expectedResult = [
	'brief' => 'this is some explanation',
	'api' => '',
	'version' => '1.14',
	'param' => 'string $s',
	'return' => 'string',
];
$actualResult = parsePhpDocLines($docBlock);

assertEquals($expectedResult, $actualResult);


