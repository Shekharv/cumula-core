<?php

define('ROOT', realpath(__DIR__ .'/../../'));

call_user_func(function() {
	require_once(implode(DIRECTORY_SEPARATOR, array(ROOT, 'cumula', 'classes', 'Autoloader.class.php')));
	Cumula\Autoloader::setup();
	new Cumula\Application(function() {
		new Cumula\Request();
		new Cumula\Response();
	
	
		$cm = new Cumula\ComponentManager();
		$config = new Cumula\SystemConfig();
			
		$config->setupListeners();
	
		$cm->startStartupComponents();
	
		$router = new Cumula\Router();
	});
});