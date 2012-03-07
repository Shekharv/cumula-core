<?php
/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @subpackage Utils
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */

//Utility function to format a string using camelcase
function toCamelCase($str, $capitalise_first_char = false)
{
	if ($capitalise_first_char) {
		$str[0] = strtoupper($str[0]);
	}
	$func = create_function('$c', 'return strtoupper($c[1]);');
	return preg_replace_callback('/_([a-z])/', $func, $str);
}

//Utility function to remove camelcase formatting on a string
function fromCamelCase($str)
{
	$str[0] = strtolower($str[0]);
	$func = create_function('$c', 'return "_" . strtolower($c[1]);');
	return preg_replace_callback('/([A-Z])/', $func, $str);
}

function object_merge($object1, $object2) {
	return (object)array_merge((array)$object1, (array)$object2);
}

function this($methodName = false, $index = 1) {
	global $thisCache;
	if(isset($thisCache) && $thisCache) {
		if($methodName && method_exists($thisCache, $methodName)) {
			return array($thisCache, $methodName);
		}
	}
	$bt = debug_backtrace();
	$frame = $bt[$index];
	$newThis = $frame['object'];
	$thisCache = $newThis;
	if($methodName) {
		return array($newThis, $methodName);
	} 
	return $newThis;
}

function copyDir($source, $destination) {
	if (is_dir($source)) {
		// Find all of the files in the directory and create directories
		// for the subdirectories
		foreach(glob($source .'/*', GLOB_NOSORT) as $file) {
			$dirname = basename($file);
			$newDestination = $destination . DIRECTORY_SEPARATOR . $dirname;
			if (is_dir($file) && is_dir($newDestination) === FALSE) {
				mkdir($newDestination, 0777, TRUE);
			}
			copyDir($file, $newDestination);
		}
	}
	else {
		// Copy the file to the public assets directory
		if(!file_exists($destination) || md5_file($source) != md5_file($destination))
			copy($source, $destination);
	}
}