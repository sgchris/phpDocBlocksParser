<?php

/** ******************************************************
 * handle the output
 */
ob_start();
register_shutdown_function(function() {
	$output = ob_get_clean();
	if (empty($output)) {
		echo "passed\n";
	} else {
		echo $output, "\n";
	}
});


/** ******************************************************
 * Basic assertion functions
 */

function message($message) {
	echo $message, "\n";
}

/**
 * check if the expression is true (not strict)
 * @param mixed $var
 * @param string $message
 */
function assertTrue($var, $message = 'assertTrue failed') {
	if (!$var) {
		message($message);
	}
}


/**
 * check if the expressions are equal (not strict)
 * @param mixed $var1
 * @param mixed $var2
 * @param string $message
 */
function assertEquals($var1, $var2, $message = false) {
	if (is_array($var1) || is_array($var2)) {
		return assertArrayEquals($var1, $var2, $message);
	}

	if ($message === false) {
		$message = 'assertEquals failed. "'.$var1.'" != "'.$var2.'"';
	}

	assertTrue($var1 == $var2, $message);
}

// compare twp arrays recursively
// from: http://stackoverflow.com/questions/3876435/recursive-array-diff
function arrayRecursiveDiff($aArray1, $aArray2) {
	$aReturn = array();

	foreach ($aArray1 as $mKey => $mValue) {
		if (array_key_exists($mKey, $aArray2)) {
			if (is_array($mValue)) {
				$aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
				if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
			} else {
				if ($mValue != $aArray2[$mKey]) {
					$aReturn[$mKey] = $mValue;
				}
			}
		} else {
			$aReturn[$mKey] = $mValue;
		}
	}
	return $aReturn;
} 

// assert that the two arrays are equal
function assertArrayEquals($arr1, $arr2, $message = false) {
	if ($message === false) {
		$message = 'assertArrayEquals failed. '.var_export($arr1, true).' not equal to '.var_export($arr2, true);
	}

	if (($arr1 && !$arr2) || (!$arr1 && $arr2)) {
		message($message);
		return;
	}

	if (is_array($arr1) && empty($arr1) && is_array($arr2) && empty($arr2)) {
		// array are equally empty
		return;
	}

	// check array diff
	$diff = arrayRecursiveDiff($arr1, $arr2);
	return assertTrue(empty($diff), $message);
}


