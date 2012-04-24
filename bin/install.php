<?php

if(isset($argv[2]) && strstr($argv[2], "-base-dir=")) {
	$path = str_replace("-base-dir=", "", $argv[2]);
	if ($path && $path[0] !== "/") {
		$path = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $path;
	}
	define('TMPROOT', realpath($path).DIRECTORY_SEPARATOR);
	if(!file_exists(TMPROOT)) {
		echo "Supplied base dir doesn't exist: $path\n";
		exit;
	}
} else {
	define('TMPROOT', realpath(__DIR__.'/../../').DIRECTORY_SEPARATOR);
}

function copyFiles($source, $destination) {
	if (is_dir($source)) {
		// Find all of the files in the directory and create directories
		// for the subdirectories
		foreach(glob($source .'/{,.}*', GLOB_BRACE|GLOB_NOSORT) as $file) {
			$dirname = basename($file);
			if ($dirname == '.' or $dirname == '..') {
				continue;
			}
			$newDestination = $destination . DIRECTORY_SEPARATOR . $dirname;
			if (is_dir($file) && is_dir($newDestination) === FALSE) {
				mkdir($newDestination, 0777, TRUE);
			}
			copyFiles($file, $newDestination);
		}
	}
	else {
		// Copy the file to the public assets directory
		if(!file_exists($destination) || md5_file($source) != md5_file($destination))
			copy($source, $destination);
	}
}

function checkVersion() {
	if(PHP_VERSION_ID < 50300) {
		echo "PHP Version must be at least 5.3.  Please update your PHP version to use Cumula.\n";
		exit;
	}
}

function checkPerms($app_name) {
	global $argv, $argc;
	//check parent dir is writable
	if(is_writable(TMPROOT)) {
		$core = "core";
		echo "Creating Project Folder...\n";
		if(!file_exists(TMPROOT.$app_name))
			mkdir(TMPROOT.$app_name);
		
		if(!file_exists(TMPROOT.$app_name.DIRECTORY_SEPARATOR.'app'))	{
			echo "Moving Files...\n";
			copyFiles(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'project_templates'.DIRECTORY_SEPARATOR.'app'), TMPROOT.$app_name.DIRECTORY_SEPARATOR);
		}
		return TMPROOT.$app_name;
	} else {
		echo "Parent folder ".realpath(TMPROOT)." is not writable.  Can not install Cumula.\n".
		"Please make this folder writable and try again.\n";
		exit;
	}		
}

echo 'Checking PHP Version: ';
checkVersion();
echo PHP_VERSION."...ok\n";
echo "Starting Install\n";
return checkPerms($argv[1]);


