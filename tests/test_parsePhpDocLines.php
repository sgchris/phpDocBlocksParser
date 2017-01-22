<?php

require_once __DIR__.'/base.php';
require_once __DIR__.'/../libs/lib.php';

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



///////////////// check empty docs
$docBlock = [
	'/**',
	' * ',
	' *',
	' */',
];
$expectedResult = [ ]; // empty array
$actualResult = parsePhpDocLines($docBlock);

assertEquals($expectedResult, $actualResult);

///////////////// check several params

$docBlock = [
	'/**',
	' * this is some explanation',
	' *',
	' * @api',
	' * @version 1.14',
	' * @param string $s1',
	' * @param string $s2',
	' * @param string $s3',
	' * @return string',
	' */',
];
$expectedResult = [
	'brief' => 'this is some explanation',
	'api' => '',
	'version' => '1.14',
	'params' => ['string $s1', 'string $s2', 'string $s3'],
	'return' => 'string',
];
$actualResult = parsePhpDocLines($docBlock);

assertEquals($expectedResult, $actualResult);


