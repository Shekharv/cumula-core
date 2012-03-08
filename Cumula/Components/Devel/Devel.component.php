<?php
namespace Cumula\Components;
Use \Cumula\Component\BaseComponent as BaseComponent;
use \Cumula\Component\Manager as ComponentManager;
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
 * Devel Component
 *
 * Provides basic debugging info, including an event trace.
 *
 * @package		Cumula
 * @subpackage	Devel
 * @author     Seabourne Consulting
 */
class Devel extends BaseComponent {
	protected $_benchmarks;
	protected $_logEventStack;
	protected $_logInstances;
	protected $_eventedInstances;
	protected $_logInstancesBefore;
	protected $_eventedInstancesBefore;
	public $_afterStartup;
	protected $_eventCount;
	
	/**
	 * Constructor.
	 * 
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();
		
		//Initialize the benchmarks container to an empty array.
		$this->_benchmarks = array();
		
		//Add  listener to app BOOT_INIT event
		A('Application')->bind('BootInit', array($this, 'startAppTimer'));
		$this->_logEventStack = '';
		$this->_logInstances = array();
		$this->_eventedInstances = array();
		$this->_eventedInstancesBefore = array();
		$this->_afterStartup = false;
		$this->_eventCount = 0;
	}
	
	
	/**
	 * Starts the application response timer, used as an event callback
	 * 
	 * @param $event
	 * @param $request
	 * @param $response
	 * @return unknown_type
	 */
	public function startAppTimer($event, $dispatcher, $request, $response) {
		A('Application')->bind('InstanceAccessed', array($this, 'logInstanceAccessed'));
		A('Application')->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch'));
		A('Response')->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch'));
		
		$this->addBenchmark('app_boot');
		A('Response')->bind('ResponsePrepare', array($this, 'stopAppTimer'));
	}
	
	/**
	 * Event listener to stop the app timer
	 * 
	 * @param $event
	 * @param $response
	 * @return unknown_type
	 */
	public function stopAppTimer($event, $response) {
		//var_dump($this->_eventedInstancesBefore);
		//die;
		$this->addBenchmark('app_shutdown');
		$time = $this->compareBenchmarks('app_boot', 'app_shutdown');
		if($this->config->getConfigValue('show_render', true)) {
			$content = '<div>Rendering the page took '.(number_format($time, 4)*1000).' ms</div>';
			$content .= '<div>Rendering the page used '.(memory_get_usage()/1000).' KB of memory</div>'; 
			$content .= '<div>Rendering the page used a maximum '.(memory_get_peak_usage()/1000).' KB of memory</div>';
			$comps = ComponentManager::instance()->getEnabledComponents();
			$content .= '<div>Rendering the page used '.count($comps).' components</div>';
			$content .= '<div>Rendering the page triggered '.$this->_eventCount.' events</div>';
			
		    $content .= '<div>Call Stack</div><pre>'.$this->_logEventStack.'</pre>';
			$response->response['content'] = str_replace('<!-- $debugOutput -->', $content, $response->response['content']);
		}
	}
	
	/* (non-PHPdoc)
	 * @see core/abstracts/BaseComponent#install()
	 */
	public function install() {
		\A('ComponentManager')->registerStartupComponent($this);
	}
	
	/* (non-PHPdoc)
	 * @see core/abstracts/BaseComponent#startup()
	 */
	public function startup() 
	{
		
		$components = A('ComponentManager')->getEnabledComponents();
		foreach($components as $component) 
		{
			if($component != get_class($this))
			{
				A($component)->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch'));
				A($component)->bind('EventListenerRegistered', array($this, 'registerPreAndPost'));
			}
		}
		
		$that = &$this;
		A('Application')->bind('AfterBootPreprocess', function() use (&$that) {
			$that->_afterStartup = true;
		}); 
		
		A('ComponentManager')->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch')); 
		A('Application')->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch')); 
		A('SystemConfig')->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch'));
		A('Router')->bind('EventDispatcherEventDispatched', array($this, 'logEventDispatch'));
	}
	
	/**
	 * Event listener
	 * 
	 * @param $event
	 * @param $event_dispatched
	 * @param $instance
	 * @param $handler
	 * @return unknown_type
	 */
	public function logEventDispatch($event, $caller, $event_dispatched, $instance, $handler = FALSE, $level = 0, $data = false) 
	{
		if(in_array($event_dispatched, array('EventDispatcherEventDispatched', 'EventListenerRegistered', 'EventDispatcherCreated', 'InstanceAccessed')))
			return;
		global $level;
		$classInstance = get_class($instance);
		$handlerClass = FALSE;
		$data_contents = 'None';
		if($data) {
			ob_start();
			var_dump($data);
			//$data_contents = ob_get_contents();
			ob_end_clean();
		}

		if(is_array($handler) && isset($handler[1]) && ($handler[1] == 'logEventDispatch' || $handler[1] == 'logInstanceAccessed')) {
			return false;
		}
		$this->_eventCount++;
		// Default Message
		$string = sprintf('%s dispatched by %s with args: %s', $event_dispatched, $classInstance, $data_contents);

		// Get the class of the handler if available
		if (is_object($handler))
		{
			$handlerClass = get_class($handler);
		}
		elseif (is_array($handler) && isset($handler[0]) && is_object($handler[0]))
		{
			$handlerClass = get_class($handler[0]);
		}

		// If the $handler and $handlerClass have been changed, build the message
		if ($handler !== FALSE && $handlerClass !== FALSE)
		{
			if(is_array($handler))
			{
				$handlerOutput = $handlerClass.'::'. $handler[1];
			} else {
				$handlerOutput = $handlerClass;
			}
			$string = sprintf('%s dispatched by %s to %s with args: %s', $event_dispatched, $classInstance, $handlerOutput, $data_contents);
			if($this->_afterStartup) {
				if(!isset($this->_eventedInstances[$classInstance]))
					$this->_eventedInstances[$classInstance] = 0;
				$this->_eventedInstances[$classInstance] = $this->_eventedInstances[$classInstance]+1;
			} 
				
		}

		$this->_logInfo("Level $level: ".$string);	
		$spacing = str_repeat('&nbsp;&nbsp;&nbsp;', $level - 1);
		$this->_logEventStack .= $spacing.$string."\n";
	}
	
	public function logInstanceAccessed($event, $dispatcher, $instance) {
		if($this->_afterStartup) {
			if(!isset($this->_logInstances[$instance]))
				$this->_logInstances[$instance] = 0;
			$this->_logInstances[$instance] = $this->_logInstances[$instance] + 1;
		}
	}

	/**
	 * Register a before and after event listener
	 * @param void
	 * @return void
	 **/
	public function registerPreAndPost($event, $dispatcher, $registeredClass, $registeredEvent) 
	{
		$notAllowed = array(
			$event,
			'EventDispatcherEventDispatched',
			'EventListenerRegistered',
			'Before',
			'After',
		);
		
		$allowRegistration = TRUE;
		foreach ($notAllowed as $string) {
			if (stripos($registeredEvent, $string) !== FALSE)
			{
				$allowRegistration = FALSE;
			}
		}
		// Don't register a before and after listener for EventListenerRegistered or event_dispatcher_event_dispatched
		if ($allowRegistration && $registeredClass !== __CLASS__)
		{
			$beforeEvent = sprintf('Before%s', $registeredEvent);
			$afterEvent = sprintf('After%s', $registeredEvent);
		}
	} // end function registerPreAndPost
	
	/**
	 * Adds a new benchmark time
	 * 
	 * @param $benchmarkName
	 * @return unknown_type
	 */
	public function addBenchmark($benchmarkName) {
		$this->_benchmarks[$benchmarkName] = microtime(true);
	}
	
	/**
	 * Compares two benchmark times
	 * 
	 * @param $benchmarkName1
	 * @param $benchmarkName2
	 * @return unknown_type
	 */
	public function compareBenchmarks($benchmarkName1, $benchmarkName2) {
		if(array_key_exists($benchmarkName2, $this->_benchmarks) && array_key_exists($benchmarkName1, $this->_benchmarks))
			return ($this->_benchmarks[$benchmarkName2]-$this->_benchmarks[$benchmarkName1]);
		else
			return false;
	}

  /**
   * Implementnation of the getInfo method
   * @param void
   * @return array
   **/
  public static function getInfo() {
    return array(
      'name' => 'Development Helper',
      'description' => 'Output Development information for each event trigger',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}

function jslog($obj) {
	$ref = new ReflectionClass(get_class($obj));
	$props = $obj->getInstanceVars();
	$json = "{'\$this(".$ref->getName().")':".json_encode($props, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP)."}";
	return "<script type='text/javascript'>console.log(".$json.");</script>";
}
