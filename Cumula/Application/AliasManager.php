<?php
namespace Cumula\Application;

class AliasManager extends EventDispatcher {	
	public $config;
	protected $_cache;
	
	public function __construct() {
		parent::__construct();
		$this->config = new StandardConfig(CONFIGROOT, 'system_aliases.yaml');
		$this->_cache = array();
		$this->setup();
	}
	
	public function setup() {
		if(!$this->config->getConfigValue('Template', false))
			$this->config->setConfigValue('Template', DEFAULT_TEMPLATE_CLASS);
			
		if(!$this->config->getConfigValue('Router', false))
			$this->config->setConfigValue('Router', DEFAULT_ROUTER_CLASS);
		
		if(!$this->config->getConfigValue('FileAggregator', false))
			$this->config->setConfigValue('FileAggregator', DEFAULT_FILEAGGREGATOR_CLASS);
		
		if(!$this->config->getConfigValue('ComponentManager', false))
			$this->config->setConfigValue('ComponentManager', DEFAULT_COMPONENT_MANAGER_CLASS);
		
		if(!$this->config->getConfigValue('Application', false))
			$this->config->setConfigValue('Application', APPLICATION_CLASS);
			
		if(!$this->config->getConfigValue('AliasManager', false))
			$this->config->setConfigValue('AliasManager', DEFAULT_ALIAS_MANAGER_CLASS);
			
		if(!$this->config->getConfigValue('Response', false))
			$this->config->setConfigValue('Response', DEFAULT_RESPONSE_MANAGER_CLASS);
			
		if(!$this->config->getConfigValue('Request', false))
			$this->config->setConfigValue('Request', DEFAULT_REQUEST_MANAGER_CLASS);
			
		if(!$this->config->getConfigValue('Renderer', false))
			$this->config->setConfigValue('Renderer', DEFAULT_RENDERER_CLASS);
			
		if(!$this->config->getConfigValue('SystemConfig', false))
			$this->config->setConfigValue('SystemConfig', DEFAULT_SYSTEM_CONFIG_CLASS);
	
		if(!$this->config->getConfigValue('Autoloader', false))
			$this->config->setConfigValue('Autoloader', DEFAULT_AUTOLOADER_CLASS);

		if(!$this->config->getConfigValue('AdminInterface', false))
			$this->config->setConfigValue('AdminInterface', DEFAULT_ADMIN_INTERFACE_CLASS);

	}
	
	public function getClassName($alias) {
		if(isset($this->_cache[$alias]))
			return $this->_cache[$alias];
		return $this->config->getConfigValue($alias, false);
	}
	
	public function setAlias($alias, $class, $remember = true) {
		if($remember)
			return $this->config->setConfigValue($alias, $class);
		else 
			$this->_cache[$alias] = $class;
	}
	
	public function setDefaultAlias($alias, $class) {
		if(!$this->getClassName($class, false))
			$this->setAlias($alias, $class);
	}
}