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
		$this->_includePath = $includePath || array();
		spl_autoload_register(array($this, 'load'));
		$this->addEvent('EventAutoload');
		$this->_setupConstants();

		$this->setFileExtension('.php');
		$this->setFileExtension('.interface.php');
		$this->setIncludePath(array(COMPROOT, CONTRIBCOMPROOT));
		
	} // end function setup

	private $_fileExtension = '.php';
	private $_namespace;
	private $_includePath;
	private $_namespaceSeparator = '\\';

	private function _setupConstants() {
		defined('APPROOT') ||
			define('APPROOT', realpath(ROOT."/..") . DIRECTORY_SEPARATOR . 'app');

		$core_path	= ROOT . DIRECTORY_SEPARATOR . 'Cumula';
		$core_component_path = ROOT . DIRECTORY_SEPARATOR; # need the full namespace
		$contrib_component_path = APPROOT . DIRECTORY_SEPARATOR . 'components';
		$config_path = APPROOT . DIRECTORY_SEPARATOR . 'config';
		$data_path = APPROOT . DIRECTORY_SEPARATOR . 'data';
		$template_path = APPROOT . DIRECTORY_SEPARATOR . 'templates';
		$test_path = $core_path . DIRECTORY_SEPARATOR . 'Test';

		defined('COMPDIRS') ||
			define('COMPDIRS', "");
		
		defined('COMPROOT') ||
			define('COMPROOT', $core_component_path . DIRECTORY_SEPARATOR);
		
		defined('CONFIGROOT') ||
			define('CONFIGROOT', $config_path . DIRECTORY_SEPARATOR);
		
		defined('DATAROOT') ||
			define('DATAROOT', $data_path . DIRECTORY_SEPARATOR);
		
		defined('CONTRIBCOMPROOT') ||
			define('CONTRIBCOMPROOT', $contrib_component_path . DIRECTORY_SEPARATOR);
		
		defined('TEMPLATEROOT') ||
			define('TEMPLATEROOT', $template_path . DIRECTORY_SEPARATOR);
		
		defined('TESTROOT') ||
			define('TESTROOT', $test_path . DIRECTORY_SEPARATOR);
		
		define('PUBLICROOT', APPROOT.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR);
		define('ASSETROOT', APPROOT.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR);
		define('LIBDIR', $core_path.DIRECTORY_SEPARATOR.'libraries');
		define('INCDIR', $core_path.DIRECTORY_SEPARATOR.'includes');
		define('BINDIR', $core_path.DIRECTORY_SEPARATOR.'bin');
	}

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
			$includePaths = $this->_includePath ?: array('');
			foreach ($includePaths as $includePath) {
				$fn = $this->tryLoadClassByPath($fileName, $className, $includePath);
				if ($fn) {
					return $fn;
				}
			}
        }
    }

	public function tryLoadClassByPath($fileName, $className, $path) {
		foreach($this->_fileExtension as $fileExtension) {
			$fn = $fileName.str_replace('_', DIRECTORY_SEPARATOR, $className) . $fileExtension;
			$unresolvedFilePath = $path . DIRECTORY_SEPARATOR . $fn;
			$filePath = stream_resolve_include_path($unresolvedFilePath);
			if ($filePath) {
				require_once $filePath;
				return $fn;
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
		$filename = $this->loadClass($className);
	} // end function load

} // end class Autoloader extends EventDispatcher
