<?php
namespace Cumula\Application;

require_once 'EventDispatcher.php';

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
	public function __construct($ns = null, $includePath = null)
    {
		parent::__construct();
        $this->_namespace = $ns;
        $this->_includePath = $includePath;
		spl_autoload_register(array($this, 'load'));
		$this->addEvent('EventAutoload');
	} // end function setup

    private $_fileExtension = '.php';
    private $_namespace;
    private $_includePath;
    private $_namespaceSeparator = '\\';


    /**
     * Sets the namespace separator used by classes in the namespace of this class loader.
     * 
     * @param string $sep The separator to use.
     */
    public function setNamespaceSeparator($sep)
    {
        $this->_namespaceSeparator = $sep;
    }

    /**
     * Gets the namespace seperator used by classes in the namespace of this class loader.
     *
     * @return void
     */
    public function getNamespaceSeparator()
    {
        return $this->_namespaceSeparator;
    }

    /**
     * Sets the base include path for all class files in the namespace of this class loader.
     * 
     * @param string $includePath
     */
    public function setIncludePath($includePath)
    {
        $this->_includePath = $includePath;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $includePath
     */
    public function getIncludePath()
    {
        return $this->_includePath;
    }

    /**
     * Sets the file extension of class files in the namespace of this class loader.
     * 
     * @param string $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
		if(!is_array($this->_fileExtension))
			$this->_fileExtension = array();
        $this->_fileExtension[] = $fileExtension;
    }

    /**
     * Gets the file extension of class files in the namespace of this class loader.
     *
     * @return string $fileExtension
     */
    public function getFileExtensions()
    {
        return $this->_fileExtension;
    }

    /**
     * Installs this class loader on the SPL autoload stack.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     *
     * @return string filename of class or null
     */
    public function loadClass($className)
    {
        if (null === $this->_namespace || $this->_namespace.$this->_namespaceSeparator === substr($className, 0, strlen($this->_namespace.$this->_namespaceSeparator))) {
            $fileName = '';
            $namespace = '';
            if (false !== ($lastNsPos = strripos($className, $this->_namespaceSeparator))) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
			foreach($this->_fileExtension as $fileExtension) {
				$fn = $fileName.str_replace('_', DIRECTORY_SEPARATOR, $className) . $fileExtension;
				$unresolvedFilePath = ($this->_includePath !== null ? $this->_includePath . DIRECTORY_SEPARATOR : '') . $fn;
				$filePath = stream_resolve_include_path($unresolvedFilePath);
				if ($filePath) {
				        require_once $filePath;
				        return $fn;
				}
			}

        }
    }

	/**
	 * Load a Autoload a class
	 * @param string $className Name of the class being loaded
	 * @return Cumula\Autoloader
	 **/
	public function load($className) 
	{
		$this->setFileExtension('.component.php');
		$this->setFileExtension('.php');
		$this->setIncludePath(realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.".."));
		$filename = $this->loadClass($className);
	} // end function load

} // end class Autoloader extends EventDispatcher
