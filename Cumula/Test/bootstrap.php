<?php


if (!defined('BASE_DIR'))
{
	if (is_callable($this, 'getProject')) {
		define('BASE_DIR', $this->getProject()->getProperty('project.basedir'));
	} else {
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
// TODO this should really come from test runner parameter?
if (file_exists(BASE_DIR . DIRECTORY_SEPARATOR . 'app'))
	setupVfs();

//require_once realpath(__DIR__."/../../bin/boot.php");

define('ROOT', realpath(__DIR__ .'/../../'));
function initialComponents()
{
	require_once(implode(DIRECTORY_SEPARATOR, array(ROOT, 'Cumula', 'Application', 'Autoloader.php')));
	new Cumula\Application\Autoloader();
	new Cumula\Application\Application();
	new Cumula\Application\AliasManager();
	new Cumula\Application\Request();
	new Cumula\Application\Response();
	
	$cm = new Cumula\Application\ComponentManager();
	$config = new Cumula\Application\SystemConfig();
	$router = new Cumula\Application\Router();
}
initialComponents();

if (ini_get('date.timezone') == '') {
    date_default_timezone_set('America/Los_Angeles');
}
