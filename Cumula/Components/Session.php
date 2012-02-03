<?php
namespace Session;
use \Cumula\BaseComponent as BaseComponent;
use \Cumula\ComponentManager as ComponentManager;

class Session extends BaseComponent {
	public function __construct() {
		parent::__construct();
	
		//Add  listener to app BOOT_INIT event
		$this->addEventListenerTo('Application', 'boot_init', 'startSession');
		$this->addEventListenerTo('Application', 'boot_shutdown', 'clearFlash');
	}
	
	public function clearFlash() {
		if(\I('Response')->response['status_code'] != 302) {
			unset($this->warning);
			unset($this->notice);
		}
	}
	
	public function startSession() {
		//session_name($this->_composeName());
		session_start();
		$this->_logInfo("session is ", $_SESSION);
	}
  
	public function endSession() {
		session_unset();
		session_destroy();
	}
	
	protected function _composeName() {
		$name = $_SERVER['REMOTE_ADDR'];
		return $name;
	}
	
	protected function _cleanUrl($url) {
		return preg_replace("/\W/", "", $url);
	}
	
	public function setValue($name, $value) {
		$_SESSION[$name] = $value;
	}
	
	public function unsetValue($name) {
		if(isset($_SESSION[$name]))
			unset($_SESSION[$name]);
	}
	
	public function getValue($name, $default = false) {
		if(empty($_SESSION[$name]))
			return $default;
		else
			return $_SESSION[$name];
	}
	
	public function install() {
		$cm = ComponentManager::instance();
		$cm->registerStartupComponent($this);
	}
	
	public function __get($name) {
		return $this->getValue($name, false);
	}
	
	public function __set($name, $value) {
		return $this->setValue($name, $value);
	}
	
	public function __isset($name) {
		return ($this->__get($name) != false);
	}
	
	public function __unset($name) {
		return $this->unsetValue($name, false);
	}
	
  /**
   * Implementation of the getInfo method
   * @param void
   * @return array
   **/
  public static function getInfo() {
    return array(
      'name' => 'Cumula Session Handler',
      'description' => 'Default Session Handler',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}
