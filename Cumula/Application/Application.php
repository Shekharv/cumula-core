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
 * Application Class
 *
 * The core application class.  This class does two main things:
 * 
 * 1. It initializes a few core classes, like the component_manager to handle plugins and extensiosn
 * 2. It works through a bootstrap proces which forms the core of the application lifecycle.
 *
 * ### Events
 * The Application Class defines the following events:
 *
 * #### BootInit
 * The first part of the boot stage, BOOT_INIT can be used by any component that registers for startup treatment with the 
 * component manager.  BOOT_INIT should be used to initialize components.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 * 
 * #### BootStartup
 * BOOT_STARTUP should be used to do startup tasks that are dependent on all classes being initialized and loaded into the 
 * global namespace.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 *
 * #### BootPrepare
 * BOOT_PREPARE should be used to collect information or generally prepare components for processing.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 *
 * #### BootPreprocess
 * BOOT_PREPROCESS can be used to filter and/or adjust functionality before the request is processed.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 *
 * #### BootProcess
 * BOOT_PROCESS should be used to run application logic and render content for display on the client browser.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 * 
 * #### BootPostprocess
 * BOOT_POSTPROCESS can be used for any cleanup that needs to happen, or filtering of rendered content.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 * 
 * #### BootCleanup
 * BOOT_CLEANUP should be used by components to perform any actions that need to be done before the output is sent to the client.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 *
 * #### BootShutdown
 * BOOT_SHUTDOWN signals that the output has been dispatched to the client.  This should be used to save settings or do any 
 * cleanup before the entire system is shutdown.
 *
 * **Args**:
 * 
 * 1. **Request**: the Request object 
 * 2. **Response**: the Response object
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
final class Application extends EventDispatcher {
	/**
	 * The boot process
	 * 
	 * @var array
	 */
	public $bootProcess = array(
			'BootInit', 
			'BootStartup', 
			'BootPrepare',
			'BootPreprocess', 
			'BootProcess', 
			'BootPostprocess', 
			'BootCleanup', 
			'BootShutdown',
	);
	
	public $currentStream;
	
	protected $_streams;
	
	/**
	 * Constructor
	 * 
	 */
	public function __construct($startupCallback = null) {
		$this->_setupBootstrap();
		$this->addEvent('InstanceAccessed');
		$this->addEvent('EventDispatcherCreated');
		$this->addEvent('GatherStreams');
		parent::__construct();
		
		$this->bind('BootStartup', array($this, 'gatherStreams'));
		
		if(is_callable($startupCallback))
			call_user_func($startupCallback);
		
		$this->boot();
	}
	
	public function gatherStreams() {
		$streams = array();
		$this->dispatch('GatherStreams', array(), function($stream) use (&$streams) {
			$streams = array_merge($streams, $stream);					
		});
		$this->_streams = $streams;
	}
	
	public function getStreams() {
		return $this->_streams;
	}
	
	/**
	 * Initializes the boot process by adding the individual steps as events
	 */
	private function _setupBootstrap() {
		//$this->addEvent('EventDispatcherCreated');
		foreach($this->bootProcess as $step) {
			$this->addEvent($step);
		}
	}
	
	/**
	 * Iterates through the boot process, triggering events for each.
	 */
	public function boot() {
		foreach($this->bootProcess as $step) {
			$this->dispatch($step, array(Request::instance(), Response::instance()));
		}
	}
}
