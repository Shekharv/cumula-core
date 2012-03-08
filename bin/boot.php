<?php

define('ROOT', realpath(__DIR__ .'/../../'));
define('CUMULAVERSION', "0.4.0");

call_user_func(function() {
	require_once(implode(DIRECTORY_SEPARATOR, array(ROOT, 'cumula', 'classes', 'Autoloader.class.php')));
	Cumula\Autoloader::setup();
	new Cumula\Application(function() {
		new Cumula\AliasManager();
		new Cumula\Request();
		new Cumula\Response();
		new Cumula\Renderer();
	
		$cm = new Cumula\ComponentManager();
		$config = new Cumula\SystemConfig();
			
		$config->setupListeners();
	
		$cm->startStartupComponents();
	
		$router = new Cumula\Router();
	});
});