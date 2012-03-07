<?php

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

function X() {
	var_dump(__NAMESPACE__);
	die;
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

