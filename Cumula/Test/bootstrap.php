<?php

if (!defined('BASE_DIR'))
{
	if (is_callable($this, 'getProject'))
	{
    define('BASE_DIR', $this->getProject()->getProperty('project.basedir'));
	}
	else {
		define('BASE_DIR', realpath(dirname(__FILE__) .'/../'));
	}
}

if (function_exists("xdebug_disable")) {
	xdebug_disable();
}

set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    BASE_DIR,
)));

require_once 'vfsStream/vfsStream.php';

function setupVfs() 
{
	vfsStream::setup('app');

	$structure = array(
		'app' => array(
			'config' => array(),
			'cache' => array(),
		),
	);

	vfsStream::create($structure);

	defined('APPROOT') ||
		define('APPROOT', vfsStream::url('app'));
	defined('CONFIGROOT') ||
		define('CONFIGROOT', vfsStream::url('app/config'));

}
setupVfs();

//require_once realpath(__DIR__."/../../bin/boot.php");

define('ROOT', realpath(__DIR__ .'/../../'));
function initialComponents()
{
	require_once(implode(DIRECTORY_SEPARATOR, array(ROOT, 'Cumula', 'Autoloader.php')));
	Cumula\Autoloader::setup();
	new Cumula\Application();
	new Cumula\AliasManager();
	new Cumula\Request();
	new Cumula\Response();
	
	$cm = new Cumula\Application\ComponentManager();
	$config = new Cumula\Config\System();
	$router = new Cumula\Router();
}
initialComponents();

if (ini_get('date.timezone') == '') {
    date_default_timezone_set('America/Los_Angeles');
}
