<?php
/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @subpackage Utils
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */

//Utility function to format a string using camelcase
function toCamelCase($str, $capitalise_first_char = false)
{
	if ($capitalise_first_char) {
		$str[0] = strtoupper($str[0]);
	}
	$func = create_function('$c', 'return strtoupper($c[1]);');
	return preg_replace_callback('/_([a-z])/', $func, $str);
}

//Utility function to remove camelcase formatting on a string
function fromCamelCase($str)
{
	$str[0] = strtolower($str[0]);
	$func = create_function('$c', 'return "_" . strtolower($c[1]);');
	return preg_replace_callback('/([A-Z])/', $func, $str);
}

function object_merge($object1, $object2) {
	return (object)array_merge((array)$object1, (array)$object2);
}

function this($methodName = false, $index = 1) {
	global $thisCache;
	if(isset($thisCache) && $thisCache) {
		if($methodName && method_exists($thisCache, $methodName)) {
			return array($thisCache, $methodName);
		}
	}
	$bt = debug_backtrace();
	$frame = $bt[$index];
	$newThis = $frame['object'];
	$thisCache = $newThis;
	if($methodName) {
		return array($newThis, $methodName);
	} 
	return $newThis;
}

function copyDir($source, $destination) {
	if (is_dir($source)) {
		// Find all of the files in the directory and create directories
		// for the subdirectories
		foreach(glob($source .'/*', GLOB_NOSORT) as $file) {
			$dirname = basename($file);
			$newDestination = $destination . DIRECTORY_SEPARATOR . $dirname;
			if (is_dir($file) && is_dir($newDestination) === FALSE) {
				mkdir($newDestination, 0777, TRUE);
			}
			copyDir($file, $newDestination);
		}
	}
	else {
		// Copy the file to the public assets directory
		if(!file_exists($destination) || md5_file($source) != md5_file($destination))
			copy($source, $destination);
	}
}

class DummyComponent {
	protected $_name;
	
	public function __construct($name) {
		$this->_name = $name;
	}
	
	public function __call($name, $args) {
		return $this->_triggerError();
	}
	
	public function __get($name) {
		return $this->_triggerError();
	}
	
	private function _triggerError() {
		trigger_error('You called an instance which doesn\'t exist: '.$this->_name);
	}
}

class ComponentProxy {
	protected $_component;
	
	public function __construct($component) {
		$this->_component = $component;
	}
	
	public function __call($name, $args) {
		if(method_exists($this->_component, $name)) {
			$proceed = true;
			$this->_component->dispatch('MethodCalled', array($name, $args), function($return) use (&$proceed) {
				if($return == false)
					$proceed = false;
			});
			if($proceed) {
				return call_user_func_array(array($this->_component, $name), $args);
			} else {
				throw new Exception('Method access denied.');
			}
		}
	}
	
	public function __get($name) {
		$var = &$this->_component->$name;
		return $var;
	}
	
	public function __set($name, $value) {
		return $this->_component->$name = $value;
	}
	
	public function __isset($name) {
		return isset($this->_component->$name);
	}
	
	public function __unset($name) {
		unset($this->_component->$name);
	}
}


function A($component) {
	$am = \Cumula\AliasManager::instance();
	if(class_exists($component))
		return $component::instance();
	if($class = $am->getClassName($component)) {
		$app = \Cumula\Application::instance();
		if($app)
			$app->dispatch('InstanceAccessed', array($class));
		return $class::instance();
	} else {
		$class = "\\$component\\$component";
		if(class_exists($class))
			return (($ins = $class::instance()) ? $ins : new DummyComponent($class));
		throw new Exception('You tried to get an alias or class which doesn\'t exist: '.$component);
	}
}

