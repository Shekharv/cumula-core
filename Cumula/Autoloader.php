<?php
namespace Cumula;

require_once 'EventDispatcher.php';
require_once 'SplClassLoader.php';

use \A as A;

/**
 * Cumula Autoloader
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 **/
class Autoloader extends EventDispatcher
{
	/**
	 * Properties
	 */
	/**
	 * Instance Variable
	 * @var Cumula\Autoloader
	 **/
	private static $instance;
	
	private static $className_cache;
	
	private static $loader;
	
	/**
	 * Cached Class map
	 * @var array
	 **/
	private $cache;
	
	/**
	 * Set up the autoloader
	 * @param void
	 * @return void
	 **/
	public static function setup() 
	{
		spl_autoload_register(array('Cumula\\Autoloader', 'load'));
		$instance = new self();
		$instance->addEvent('EventAutoload');
		static::$className_cache = array();
	} // end function setup

	/**
	 * Load a Autoload a class
	 * @param string $className Name of the class being loaded
	 * @return Cumula\Autoloader
	 **/
	public static function load($className) 
	{
		$loader = new \SplClassLoader();
		$loader->setFileExtension('.component.php');
		$loader->setFileExtension('.php');
		$loader->setIncludePath(realpath(__DIR__.DIRECTORY_SEPARATOR.".."));
		$filename = $loader->loadClass($className);
	} // end function load

} // end class Autoloader extends EventDispatcher
