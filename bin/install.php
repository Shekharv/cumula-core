<?php

define('TMPROOT', realpath(__DIR__ .'/../../'));

define('CUMULAVERSION', "0.4.0");

function checkVersion() {
	if(PHP_VERSION_ID < 50300) {
		echo "PHP Version must be at least 5.3.  Please update your PHP version to use Cumula.\n";
		exit;
	}
}

function checkPerms() {
	//check parent dir is writable
	if(is_writable(realpath(__DIR__."/.."))) {
		mkdir(realpath(__DIR__."/../".$argv[2]));
		rename(realpath(__DIR__), realpath(__DIR__."/../".$argv[2].DIRECTORY_SEPARATOR.basename(__DIR__)));
	} else {
		echo "Parent folder ".realpath(__DIR__."/..")." is not writable.  Can not install Cumula.\n".
		"Please make this folder writable and try again.\n";
		exit;
	}
	if(!file_exists(TMPROOT.DIRECTORY_SEPARATOR.'app')) {
		
	}
		
}

echo 'Checking PHP Version: ';
checkVersion();
echo PHP_VERSION."...ok\n";
checkPerms();
echo "Starting Install...\n";
echo "Booting Cumula...\n";
$argv[1] = 'install';