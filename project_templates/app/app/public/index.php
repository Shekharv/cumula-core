<?php

$loader = require '../../vendor/.composer/autoload.php';

function getComponentDirs($prefixes) {
	$ret = array();
	foreach($prefixes as $name => $prefs) {
		if ($name != 'Cumula') {
			$ret = array_merge($ret, $prefs);
		}
	}
	return $ret;
}

define('APPROOT', realpath(implode(DIRECTORY_SEPARATOR, array(__DIR__, '..'))));
define('COMPDIRS', implode("|",getComponentDirs($loader->getPrefixes())));

include(realpath(implode(DIRECTORY_SEPARATOR, array(APPROOT, '..', 'vendor', 'cumula', 'core', 'bin', 'boot.php'))));