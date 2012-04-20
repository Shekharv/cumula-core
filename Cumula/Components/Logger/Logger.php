<?php
namespace Cumula\Components\Logger;
use \Cumula\Base\Component as BaseComponent;
use \Cumula\Config\System as SystemConfig;
use \Cumula\Schema\Simple as SimpleSchema;
use \Cumula\Application\ComponentManager as ComponentManager;
use \Cumula\DataStore\TextFile\WriteOnly as WriteOnlyTextDataStore;
use \A as A;

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
		
		$logDir = A('SystemConfig')->getValue('logDirectory', APPROOT.'/log');
		$logFile = A('SystemConfig')->getValue('environment', ENV_DEVELOPMENT).'.log';
		$this->syslog = A('SystemConfig')->getValue('syslog', FALSE);
		
		if (!file_exists($logDir))
			mkdir($logDir);
			
		$this->dataStore = new WriteOnlyTextDataStore(array(
			'fields' => array('id' => 'string'),
			'idField' => 'id',
			'filename' => $logDir, 
			'sourceDir' => $logFile
		));
		
		A('Application')->bind('EventDispatcherCreated', array($this, 'registerForEvents'));
	}
	
	public function registerForEvents($event, $dispatcher, $comp) 
	{
		$comp::instance()->bind('EventLogged', array($this, 'logMessage'));
	}
	
	public function install() 
	{
		A('ComponentManager')->registerStartupComponent($this);
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
