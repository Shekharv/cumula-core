<?php
namespace Cumula;

require_once realpath(dirname(__FILE__) .'/EventDispatcher.class.php');

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
		$instance->addEvent('event_autoload');
		$instance->addEventListenerTo('Cumula\\Autoloader', 'event_autoload', array($instance, 'defaultAutoloader'));
		$instance->addEventListenerTo('Cumula\\Autoloader', 'event_autoload', array($instance, 'libraryAutoloader'));
	} // end function setup

	/**
	 * Load a Autoload a class
	 * @param string $className Name of the class being loaded
	 * @return Cumula\Autoloader
	 **/
	public static function load($className) 
	{
		$instance = self::instance();
		// If we don't already know about the class, dispatch the event to find it.
		if (($classFile = $instance->classExists($className)) === FALSE)
		{
			$instance->dispatch('event_autoload', array($className), 'registerClasses');
			if (($classFile = $instance->classExists($className)) === FALSE)
			{
				return FALSE;
			}
		}
		require_once($classFile);
		return $instance;
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
	 * Default Autoloader Method
	 * @param void
	 * @return array Returns an associative array of classes and the files they are found in.
	 **/
	public function defaultAutoloader($event, $dispatcher, $className) 
	{
		$basedir = realpath(dirname(__FILE__) .'/../');
		$dir = realpath(dirname(__FILE__));
		$libDir = realpath($basedir .'/libraries/');
		$interfaceDir = realpath($basedir .'/interfaces/');
		return array(
			// Core Classes
			'Cumula\\Autoloader' => $dir .'/Autoloader.class.php',
			'Cumula\\Application' => $dir. '/Application.class.php',
			'Cumula\\EventDispatcher' => $dir .'/EventDispatcher.class.php',
			'Cumula\\BaseComponent' => $dir .'/BaseComponent.class.php',
			'Cumula\\Request' => $dir .'/Request.class.php',
			'Cumula\\Response' => $dir .'/Response.class.php',
			'Cumula\\Router' => $dir .'/Router.class.php',
			'Cumula\\ComponentManager' => $dir .'/ComponentManager.class.php',
			'Cumula\\SimpleSchema' => $dir .'/SimpleSchema.class.php',
			'Cumula\\BaseDataStore' => $dir .'/BaseDataStore.class.php',
			'Cumula\\BaseAPIDataStore' => $dir .'/BaseAPIDataStore.class.php',
			'Cumula\\BaseSqlDataStore' => $dir .'/BaseSqlDataStore.class.php',
			'Cumula\\BaseMVCComponent' => $dir .'/BaseMVCComponent.class.php',
			'Cumula\\BaseMVCController' => $dir .'/BaseMVCController.class.php',
			'Cumula\\BaseMVCModel' => $dir .'/BaseMVCModel.class.php',
			'Cumula\\SystemConfig' => $dir .'/SystemConfig.class.php',
			'Cumula\\BaseSchema' => $dir .'/BaseSchema.class.php',
			'Cumula\\Renderer' => $dir .'/Renderer.class.php',
			'Cumula\\Error' => $dir .'/Error.class.php',
			'I' => $dir .'/I.class.php',

			// Exceptions
			'Cumula\\EventException' => $dir .'/Exception/EventException.class.php',
			'Cumula\\DataStoreException' => $dir .'/Exception/DataStoreException.class.php',

			// Interfaces
			'Cumula\\CumulaConfig' => $interfaceDir .'/CumulaConfig.interface.php',
			'Cumula\\CumulaSchema' => $interfaceDir .'/CumulaSchema.interface.php',
			'Cumula\\CumulaTemplater' => $interfaceDir .'/CumulaTemplater.interface.php',
		);
	} // end function defaultAutoloader

	/**
	 * Autoloader Function for the libraries
	 * For libraries, the namespace is the directory and the class is the filename (without .class.php)
	 * 	ie. the file libraries/MyLibrary/MyLibrary.class.php will contain the class MyLibrary\MyLibrary
	 * 		and libraries/MyLibrary/SomeOtherFile.class.php will have the MyLibrary\SomeOtherFile class
	 * @param string $event Event being dispatched
	 * @param Cumula\Autoloader $dispatcher Object dispatching the event
	 * @param string $className className being loaded
	 * @return void
	 **/
	public function libraryAutoloader($event, $dispatcher, $className) 
	{
		$libraryPath = realpath(dirname(__DIR__) .'/libraries');

		$files = glob(sprintf('%s/*/*.class.php', $libraryPath), GLOB_NOSORT);
		$classes = array();
		foreach ($files as $file) 
		{
			$class = sprintf('%s\\%s', basename(dirname($file)), basename($file, '.class.php'));
			if (stripos($file, str_replace('\\', '/', $class)) !== 0) 
			{
				$classes[$class] = $file; 
			}
		}
		return $classes;
	} // end function libraryAutoloader
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
			throw new \Exception(sprintf('Trying to overwrite the Autoloader Cache for %s', $className));
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
					$instance->dispatch('event_autoload', array($className), 'registerClasses');
					$class = __CLASS__;
					return $class::absoluteClassName($className, TRUE);
				}
			}
			elseif (count($classes) > 1)
			{
				unset($classes['Cumula']);
				ksort($classes);
			}

			return array_shift($classes);
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
