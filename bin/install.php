<?php

define('TMPROOT', realpath(__DIR__ .'/../../').DIRECTORY_SEPARATOR);

define('CUMULAVERSION', "0.4.0");

function checkVersion() {
	if(PHP_VERSION_ID < 50300) {
		echo "PHP Version must be at least 5.3.  Please update your PHP version to use Cumula.\n";
		exit;
	}
}

function checkPerms() {
	global $argv, $argc;
	//check parent dir is writable
	if(is_writable(TMPROOT)) {
		echo "Creating Project Folder...\n";
		mkdir(TMPROOT.$argv[1]);
		mkdir(TMPROOT.$argv[1].DIRECTORY_SEPARATOR.'app');
		echo "Moving Files...\n";
		rename(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'), TMPROOT.$argv[1].DIRECTORY_SEPARATOR.basename(realpath(__DIR__.DIRECTORY_SEPARATOR.'..')));
	} else {
		echo "Parent folder ".realpath(__DIR__."/..")." is not writable.  Can not install Cumula.\n".
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