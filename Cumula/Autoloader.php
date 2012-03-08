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
		$filename = $loader->loadClass($className);
	} // end function load

	/**
	 * Determine whether or not a class is in the autoloader
	 * @param string $className Name of the class being checked
	 * @return boolean
	 **/
	public function classExists($className) 
	{
		$cache = $this->getCache();
		return isset($cache[$className]) ? $cache[$className] : FALSE;
	} // end function classExists

	/**
	 * Register a class with the autoloader
	 * @param string $className Name of the class being registered
	 * @param string $classFile File where the class can be found
	 * @return Cumula\Autoloader
	 **/
	public function registerClass($className, $classFile) 
	{
		$cache = $this->getCache();
		if (!isset($cache[$className]))
		{
			$cache[$className] = $classFile;
			$this->setCache($cache);
		}
		elseif ($cache[$className] != $classFile)
		{
			throw new \Exception(sprintf('Trying to overwrite the Autoloader Cache for %s: was %s with %s',
										 $className,
										 $cache[$className],
										 $classFile));
		}
		return $this;
	} // end function registerClass

	/**
	 * Register multiple classes at once
	 * @param array $classArray Array of ClassName => ClassFile values
	 * @return void
	 **/
	public function registerClasses(array $classArray) 
	{
		$cache = $this->getCache();
		$this->setCache(array_merge($cache, $classArray));
	} // end function registerClasses

	/**
	 * Get the Absolute Class name rather than a realative class name
	 * @param string $className Relative Class Name (without namespace)
	 * @return string Absolute ClassName (with namespace);
	 **/
	public static function absoluteClassName($className, $secondCall = FALSE) 
	{
		if(isset(static::$className_cache[$className]))
			return static::$className_cache[$className];
		$instance = self::instance();
		$cache = $instance->getCache();
		if (isset($cache[$className]) || $className == __CLASS__)
		{
			return $className;
		}
		$classes = array();
		foreach ($cache as $class => $file)
		{
			$classArr = explode('\\', $class);
			if ($classArr[count($classArr) - 1] == $className)
			{
				$classes[$classArr[0]] = $class;
			}
		}

		if (count($classes) === 0)
		{
			if ($secondCall)
			{
				return FALSE;
			}
			else 
			{
				$instance->dispatch('EventAutoload', array($className), 'registerClasses');
				$class = __CLASS__;
				return $class::absoluteClassName($className, TRUE);
			}
		}
		elseif (count($classes) > 1)
		{
			unset($classes['Cumula']);
			ksort($classes);
		}
		$return = array_shift($classes);
		static::$className_cache[$className] = $return;
		return $return;
	} // end function absoluteClassName
	/**
	 * Getters and Setters
	 */
	/**
	 * Getter for $this->cache
	 * @param void
	 * @return array
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	private function getCache() 
	{
		if (is_null($this->cache))
		{
			$this->setCache(array());
		}
		return $this->cache;
	} // end function getCache()
	
	/**
	 * Setter for $this->cache
	 * @param array
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	private function setCache($arg0) 
	{
		$this->cache = $arg0;
		return $this;
	} // end function setCache()

} // end class Autoloader extends EventDispatcher
