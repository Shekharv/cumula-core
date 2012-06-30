<?php
namespace Cumula\Application;

class AliasManager extends EventDispatcher {	
	public $config;
	protected $_cache;
	
	public function __construct() {
		parent::__construct();
		$this->config = new StandardConfig(CONFIGROOT, 'system_aliases.yaml');
		$this->_cache = array();
	}

	public $defaults = array(
		'Template' => DEFAULT_TEMPLATE_CLASS,
		'Router' => DEFAULT_ROUTER_CLASS,
		'FileAggregator' => DEFAULT_FILEAGGREGATOR_CLASS,
		'DSWebAPI' => DEFAULT_WEBAPI_CLASS,
		'ComponentManager' => DEFAULT_COMPONENT_MANAGER_CLASS,
		'Application' => APPLICATION_CLASS,
		'AliasManager' => DEFAULT_ALIAS_MANAGER_CLASS,
		'Response' => DEFAULT_RESPONSE_MANAGER_CLASS,
		'Request' => DEFAULT_REQUEST_MANAGER_CLASS,
		'Renderer' => DEFAULT_RENDERER_CLASS,
		'SystemConfig' => DEFAULT_SYSTEM_CONFIG_CLASS,
		'Autoloader' => DEFAULT_AUTOLOADER_CLASS,
		'AdminInterface' => DEFAULT_ADMIN_INTERFACE_CLASS,
		'FormHelper' => DEFAULT_FORMHELPER_CLASS,
		);
	
	public function getClassName($alias) {
		if(isset($this->_cache[$alias]))
			return $this->_cache[$alias];
		return $this->config->getConfigValue($alias, array_get($alias, $this->defaults, false));
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