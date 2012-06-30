<?php
namespace Cumula\Application;
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
class SystemConfig extends \Cumula\Base\Component {
	public function __construct() {
		parent::__construct();
		$this->config = new \Cumula\Application\StandardConfig(CONFIGROOT, 'system.yaml');
		
		$this->addEvent('SystemConfigSetValue');
		$this->addEvent('SystemConfigGetValue');
		
		$this->_output = array();
	}
	
	public function setupListeners() {
		A('ComponentManager')->bind('ComponentStartupComplete', array($this, 'startup'));
	}
	
	public $defaults = array(
		SETTING_DEFAULT_BASE_PATH => DEFAULT_SITE_BASE_PATH,
		SETTING_ENVIRONMENT => DEFAULT_ENVIRONMENT,
		SETTING_SITE_TITLE => DEFAULT_SITE_TITLE,
		SETTING_DEFAULT_DATASTORE => DEFAULT_DATASTORE_CLASS,
		SETTING_DEFAULT_CONFIG => DEFAULT_CONFIG_CLASS,
		);
	
	/**
	 * Implements the BaseComponent startup function
	 * 
	 */

	public function startup() {
		A('AdminInterface')->bind('GatherAdminPages', array(
			'Core Settings' => array(
				'config' => $this->config,
				'fields' => array(
					SETTING_DEFAULT_BASE_PATH => array( 
						'title' => 'Base Path',
						'type' => 'string',
						'value' => $this->config->getConfigValue(SETTING_DEFAULT_BASE_PATH)),
					SETTING_SITE_URL => array(
						'title' => 'Base URL',
						'type' => 'string',
						'value' => $this->config->getConfigValue(SETTING_SITE_URL, '')),	
					SETTING_ENVIRONMENT => array(
						'title' => 'Environment',
						'type' => 'select',
						'values' => array("Development" => ENV_DEVELOPMENT, "Test" => ENV_TEST, "Production" => ENV_PRODUCTION),
						'selected' => $this->config->getConfigValue(SETTING_ENVIRONMENT)),
				),
			)
		));
	}
	
	/**
	 * Saves a new setting and value
	 * 
	 * @param $config
	 * @param $value
	 * @return unknown_type
	 */
	public function setValue($config, $value) {
		$this->dispatch('SystemConfigSetValue', array($config, $value));
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
		$value = $this->config->getConfigValue(
			$config,
			array_get($config, $this->defaults, $default)
			);
		$this->dispatch('SystemConfigGetValue', array($config, $value));
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
