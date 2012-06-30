<?php

define('ROOT', realpath(__DIR__ .'/../'));
define('CUMULAVERSION', "0.6.1");

call_user_func(function() {
	require_once(implode(DIRECTORY_SEPARATOR, array(ROOT, 'Cumula', 'Application', 'Autoloader.php')));
	new Cumula\Application\Autoloader();
	new Cumula\Application\Application(function() {
		new Cumula\Application\AliasManager();
		new Cumula\Application\Request();
		new Cumula\Application\Response();
		new Cumula\Application\Renderer();
	
		$cm = new Cumula\Application\ComponentManager();
		$config = new Cumula\Application\SystemConfig();
			
		$config->setupListeners();
	
		$cm->startStartupComponents();
	
		$router = new Cumula\Application\Router();
		new Cumula\Application\HTMLStream();
		new Cumula\Application\CLIStream();
	});
});