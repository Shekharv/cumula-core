<?php
namespace Cumula;
/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */

/**
 * SystemConfig Class
 *
 * The main storage for system wide configuration settings.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
class SystemConfig extends BaseComponent {
	public function __construct() {
		parent::__construct();
		$this->config = new \StandardConfig\StandardConfig(CONFIGROOT, 'system.yaml');
		
		$this->addEvent('systemconfig_set_value');
		$this->addEvent('systemconfig_get_value');
		
		$this->setupDefaults();
		
		$this->_output = array();
	}
	
	public function setupListeners() {
		$this->addEventListenerTo('Cumula\\ComponentManager', 'component_startup_complete', 'startup');
	}
	
	
	/**
	 * Creates default values for settings if no other value exists.
	 * 
	 * @return unknown_type
	 */
	public function setupDefaults() {
		if(!$this->config->getConfigValue(SETTING_TEMPLATER))
			$this->config->setConfigValue(SETTING_TEMPLATER, DEFAULT_TEMPLATER_CLASS);
			
		if(!$this->config->getConfigValue(SETTING_ROUTER))
			$this->config->setConfigValue(SETTING_ROUTER, DEFAULT_ROUTER_CLASS);

		if(!$this->config->getConfigValue(SETTING_DEFAULT_DATASTORE))
			$this->config->setConfigValue(SETTING_DEFAULT_DATASTORE, DEFAULT_DATASTORE_CLASS);

		if(!$this->config->getConfigValue(SETTING_DEFAULT_CONFIG))
			$this->config->setConfigValue(SETTING_DEFAULT_CONFIG, DEFAULT_CONFIG_CLASS);	
		
		if(!$this->config->getConfigValue(SETTING_COMPONENT_MANAGER))
			$this->config->setConfigValue(SETTING_COMPONENT_MANAGER, DEFAULT_COMPONENT_MANAGER_CLASS);
			
		if(!$this->config->getConfigValue(SETTING_DEFAULT_BASE_PATH))
			$this->config->setConfigValue(SETTING_DEFAULT_BASE_PATH, DEFAULT_SITE_BASE_PATH);	
			
		if(!$this->config->getConfigValue(SETTING_ENVIRONMENT))
			$this->config->setConfigValue(SETTING_ENVIRONMENT, DEFAULT_ENVIRONMENT);
			
		if(!$this->config->getConfigValue(SETTING_SITE_TITLE))
			$this->config->setConfigValue(SETTING_SITE_TITLE, DEFAULT_SITE_TITLE);		
	}
	
	/**
	 * Implements the BaseComponent startup function
	 * 
	 */

	public function startup() {
		$this->addEventListenerTo('AdminInterface\\AdminInterface', 'admin_collect_settings_pages', 'setupAdminPages');
	}
	
	/**
	 * Sets the admin pages for the system settings.
	 * 
	 */

	public function setupAdminPages($event, $am) {
		$page = $am->newAdminPage();
		$page->title = 'Site Settings';
		$page->description = 'Basic Site Settings.';
		$page->route = '/admin/site_settings';
		$page->fields = array(array('name' => SETTING_DEFAULT_BASE_PATH, 
									'title' => 'Base Path',
									'type' => 'string',
									'value' => $this->config->getConfigValue(SETTING_DEFAULT_BASE_PATH)),
							  array('name' => SETTING_SITE_URL, 
									'title' => 'Base URL',
									'type' => 'string',
									'value' => $this->config->getConfigValue(SETTING_SITE_URL, '')),
							  array('name' => SETTING_SITE_TITLE, 
										'title' => 'Site Title',
										'type' => 'string',
										'value' => $this->config->getConfigValue(SETTING_SITE_TITLE)),		
							  array('name' => SETTING_ENVIRONMENT, 
									'title' => 'Environment',
									'type' => 'select',
									'values' => array("Development" => ENV_DEVELOPMENT, "Test" => ENV_TEST, "Production" => ENV_PRODUCTION),
									'selected' => $this->config->getConfigValue(SETTING_ENVIRONMENT)),
							);		
		$page->component = &$this;
		$am->addAdminPage($page);
		
		$page = $am->newAdminPage();
		$page->title = 'Component Defaults';
		$page->route = '/admin/component_defaults';
		$page->description = 'Set the default classes used in Cumula.  WARNING: only edit this page if you know what you are doing!';
		$page->fields = array(array('name' => SETTING_COMPONENT_MANAGER, 
									'title' => 'Component Manager Class',
									'type' => 'string',
									'value' => $this->config->getConfigValue(SETTING_COMPONENT_MANAGER)),
								array('name' => SETTING_TEMPLATER, 
									'title' => 'Templater Class',
									'type' => 'string',
									'value' => $this->config->getConfigValue(SETTING_TEMPLATER)),	
								array('name' => SETTING_ROUTER, 
									'title' => 'Router Class',
									'type' => 'string',
									'value' => $this->config->getConfigValue(SETTING_ROUTER)),
								array('name' => SETTING_DEFAULT_DATASTORE, 
									'title' => 'Default DataStore Class',
									'type' => 'string',
									'value' => $this->config->getConfigValue(SETTING_DEFAULT_DATASTORE)),
								array('name' => SETTING_DEFAULT_CONFIG, 
										'title' => 'Default Config Class',
										'type' => 'string',
										'value' => $this->config->getConfigValue(SETTING_DEFAULT_CONFIG)),
							);		
		$page->component = &$this;
		$am->addAdminPage($page);
	}
	
	/**
	 * Saves a new setting and value
	 * 
	 * @param $config
	 * @param $value
	 * @return unknown_type
	 */
	public function setValue($config, $value) {
		$this->dispatch('systemconfig_set_value', array($config, $value));
		$this->config->setConfigValue($config, $value);
	}
	
	/**
	 * Retrieves an existing value.  If the value doesn't exist, the default value is used.
	 * 
	 * @param $config
	 * @param $default
	 * @return unknown_type
	 */
	public function getValue($config, $default = null) {
		$value = $this->config->getConfigValue($config, $default);
		$this->dispatch('systemconfig_get_value', array($config, $value));
		return $value;
	}
    /**
     * Implementation of getInfo
     * @param void
     * @return array
     **/
    public static function getInfo() {
        
    } // end function getInfo
}
