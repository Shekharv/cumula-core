<?php

if(isset($argv[2]) && strstr($argv[2], "-base-dir=")) {
	$path = str_replace("-base-dir=", "", $argv[2]);
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
		foreach(glob($source .'/*', GLOB_NOSORT) as $file) {
			$dirname = basename($file);
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

function checkPerms() {
	global $argv, $argc, $installPath;
	//check parent dir is writable
	if(is_writable(TMPROOT)) {
		$core = "core";
		echo "Creating Project Folder...\n";
		if(!file_exists(TMPROOT.$argv[1]))
			mkdir(TMPROOT.$argv[1]);
		if(!file_exists(TMPROOT.$argv[1].DIRECTORY_SEPARATOR.'app'))
			mkdir(TMPROOT.$argv[1].DIRECTORY_SEPARATOR.'app');
		
		if(!file_exists(TMPROOT.$argv[1].DIRECTORY_SEPARATOR.$core))	{
			echo "Moving Files...\n";
			copyFiles(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'), TMPROOT.$argv[1].DIRECTORY_SEPARATOR.$core);
		}
		$installPath = TMPROOT.$argv[1].DIRECTORY_SEPARATOR.$core;
	} else {
		echo "Parent folder ".realpath(TMPROOT)." is not writable.  Can not install Cumula.\n".
		"Please make this folder writable and try again.\n";
		exit;
	}		
}

echo 'Checking PHP Version: ';
checkVersion();
echo PHP_VERSION."...ok\n";
checkPerms();
echo "Starting Install...\n";
echo "Booting Cumula for the first time...\n";
$argv[1] = 'setup';
if(isset($argv[2]))
	unset($argv[2]);

