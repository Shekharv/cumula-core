<?php
namespace Logger;
use Cumula\BaseComponent as BaseComponent;
use Cumula\SystemConfig as SystemConfig;
use Cumula\SimpleSchema as SimpleSchema;
use Cumula\ComponentManager as ComponentManager;

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

require_once dirname(__FILE__).'/includes/WriteOnlyTextDataStore.class.php';

/**
 * Logger Component
 *
 * Logs messages to the specified output file/service.
 *
 * @package		Cumula
 * @subpackage	Logger
 * @author     Seabourne Consulting
 */
class Logger extends BaseComponent 
{
	/**
	 * @return unknown_type
	 */
	protected $_registered;
	
	public function __construct() 
	{
		parent::__construct();
		$this->_registered = array();
		
		$log_dir = SystemConfig::instance()->getValue('log_directory', APPROOT.'/log');
		$log_file = SystemConfig::instance()->getValue('environment', ENV_DEVELOPMENT).'.log';
		$this->syslog = SystemConfig::instance()->getValue('syslog', FALSE);
		if (!file_exists($log_dir))
		{
			mkdir($log_dir);
		}
		$schema = new SimpleSchema(array('id' => 'string'), 'id', 'log');
		$this->dataStore = new WriteOnlyTextDataStore($schema, array('logfile' => $log_dir .'/'.$log_file));
		$this->addEventListenerTo('Application', 'event_dispatcher_created', 'registerForEvents');
	}
	
	public function registerForEvents($event, $dispatcher, $comp) 
	{
		$this->addEventListenerTo($comp, 'event_logged', 'logMessage');
	}
	
	public function install() 
	{
		$cm = ComponentManager::instance();
		$cm->registerStartupComponent($this);
	}
	
	/**
	 * @param $level
	 * @param $message
	 * @param $other_info
	 * @return unknown_type
	 */
	public function logMessage($event, $dispatcher, $level, $message, $other_info = null) 
	{
		if ($other_info) 
		{
			$other_info = "\nArgs:\n ".var_export($other_info, true);
		}

		if (is_array($other_info) && count($other_info) == 0)
		{
			$other_info = NULL;
		}

		$this->dataStore->create(array($level, $message, $other_info));

		// Output to the system log if configured
		if ($this->syslog && $this->syslog == 'true') 
		{
			syslog(LOG_NOTICE, $message);
		}
	}

  /**
   * Implementation of the getInfo method
   * @param void
   * @return array
   **/
	public static function getInfo() 
	{
    return array(
      'name' => 'Cumula Logger',
      'description' => 'Default Logger Class for Cumula',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}
