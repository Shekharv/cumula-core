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