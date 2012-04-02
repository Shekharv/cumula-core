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
require_once(realpath(implode(DIRECTORY_SEPARATOR, array(
	__DIR__,
	"..",
	'includes',
	'core.inc',
))));

/**
 * EventDispatcher Class
 *
 * The base class that handles event registration and dispatching.  This serves as the base class for most classes
 * in the Cumula Framework
 *
 * ### Events
 * The EventDispatcher defines the following events:
 *
 * #### EVENTDISPATCHER_EVENT_DISPATCHED
 * This is a type of meta-event, dispatched whenever another event is dispatched to a particular listener.  If there are 
 * multiple listeners for an event, this event will be dispatched multiple times.
 *
 * **Args**:
 * 
 * 1. **Event**: the event dispatched.
 * 2. **Dispatcher**: the original dispatcher.
 * 3. **Event Listener**: the listener the event was dispatched to.
 * 4. **Level**: the event stack level
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
class EventDispatcher {
	protected static $_instances = array();
	
	/**
	 * Has containing the Listeners
	 * @var array
	 **/
	protected $eventHash = array();
	
	/**
	 * Constructor.  Sets the default global $level to 0.
	 */
	public function __construct() 
	{
		self::setInstance($this);
		
		global $level;
		if (!isset($level))
		{
			$level = 0;
		}
		$this->addEvent('EventDispatcherEventDispatched');
		$this->addEvent('EventListenerRegistered');
		$this->addEvent('EventLogged');
		$disallow = array("Cumula\\EventDispatcher",
							"Cumula\\Autoloader",
							"Cumula\\Application");
		if (class_exists("\\Cumula\\Application") && !in_array(get_called_class(), $disallow)) 
		{
			$app = \Cumula\Application::instance();
			if($app)
			{
				$app->dispatch('EventDispatcherCreated', array(get_called_class()));
			}
		}
	}
	
	/**
	 * Registers an event in the internal registry.  Raises an exception if trying to re-register an existing event.  This ensures
	 * that components don't unwittingly use the same event title.
	 * 
	 * @param	string	The event to add to the registry.
	 */
	public function addEvent($event) 
	{
		if (!isset($this->eventHash[$event]))
		{
			$this->eventHash[$event] = array();
		}
	}

	/**
	 * Removes an event from the registry table.
	 * 
	 * @param	string	The event to remove from the registry.
	 */
	public function removeEvent($event) 
	{
		if (isset($this->eventHash[$event])) 
		{
			unset($this->eventHash[$event]);
		}
	}
	
	public function bind($event, $callback) {
		$this->addEvent($event);
		if (!in_array($callback, $this->eventHash[$event]))
		{
			$this->eventHash[$event][] = $callback;
		}
		$this->dispatch('EventListenerRegistered', array($event, $callback));
	}
	
	public function chain($startEvent, $dispatchEvent, $callback) {
		$that = $this;
		$this->bind($startEvent, function() use ($that, $dispatchEvent, $callback) {
			$that->dispatch($dispatchEvent, $callback);
		});
	}

	/**
	 * Given an event and handler, removes any matching entry in the event registry
	 * 
	 * @param	string The event to remove handler from.
	 * @param	function	a function, or an array containing the class and method, or a closure to remove.
	 */
	public function unbind($event, $handler) 
	{
		if ($this->eventIsRegistered($event))
		{
			foreach ($this->eventHash[$event] as $key => $listener)
			{
				if ($listener === $handler)
				{
						unset($this->eventHash[$event][$key]);
						return;
				}
			}
		}
	}
	
	public function unbindAll($event) 
	{
		if (isset($this->eventHash[$event])) 
		{
			$this->eventHash[$event] = array();
		}
	}
	
	/**
	 * Dispatches an event.  Data must be an array of variables that will be passed to any registered event handler.
	 * 
	 * @param	string	The event to dispatch
	 * @param	array 	An optional array or arguments to pass to the Event Listeners
	 * @param	callable	An optional callback that the return is passed to
	 */
	public function dispatch($event, $data = array(), $callback = false) 
	{
		$fireBeforeAndAfter = (stripos($event, 'Before') === FALSE) && (stripos($event, 'After') === FALSE);
		$beforeEvent = sprintf('Before%s', $event);
		$afterEvent = sprintf('After%s', $event);
		$isNormalEvent = ($event != 'EventDispatcherEventDispatched');
		$hash = $this->eventHash;
		if(is_callable($data)) {
			$callback = $data;
			$data = array();
		}
		//if $callback is a string, wrap it as a callable array with $this
		if (is_string($callback))
		{
			$callback = array($this, $callback);
		}
		
		array_unshift($data, $event, $this);
		$original_data = array_slice($data, 2);
		
		global $level;
		
		if ($isNormalEvent && $fireBeforeAndAfter)
		{
			$level++;
		}
			
		if ($fireBeforeAndAfter && $this->getEventListeners($beforeEvent))
		{
			$this->dispatch($beforeEvent, $original_data, $callback);
		}
		$listeners = $this->getEventListeners($event);
		if($listeners) {
			//For each listener call the handler function
			foreach ($listeners as $event_handler) 
			{
				if ($isNormalEvent) 
				{
					$this->dispatch('EventDispatcherEventDispatched', array($event, $this, $event_handler, $level, $original_data));
				}
				//If event handler is a callback, save the result of the callback
				if(is_callable($event_handler))
					$result = call_user_func_array($event_handler, $data);
				else //otherwise, the callback is a value, and just use that
					$result = $event_handler;
				
				if($callback)
				{
					call_user_func($callback, $result);
				}
			}
		}
		if ($fireBeforeAndAfter && $this->getEventListeners($afterEvent))
		{
			$this->dispatch($afterEvent, $original_data, $callback);
		}
		
		if ($isNormalEvent && $fireBeforeAndAfter)
		{
			$level--;
		}
		return true;
	}

	 
	/**
	 * Get the events for the current class
	 * @param void
	 * @return mixed
	 **/
	public function getEvents() 
	{
		return $this->eventHash;
	}
	
	public function eventIsRegistered($event) 
	{
		$eventHash = $this->eventHash;
		return isset($eventHash[$event]);
	}
	
	public function getEventListeners($event) 
	{
		$eventHash = $this->eventHash;
		if ($this->eventIsRegistered($event)) {
			return $eventHash[$event];
		}
		return FALSE;
	}

	/**
	 * Getters and Setters
	 */
	/**
	 * Returns the instance of the static class.
	 * 
	 * @return BaseComponent|bool	The instance, if it exists, otherwise false
	 */
	public static function instance() 
	{
		$class = get_called_class();
		if (!isset(self::$_instances[$class]) || is_null(self::$_instances[$class]))
		{
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}
	
	/**
	 * Sets the instance of a class
	 * 
	 * @param	BaseComponent	The instance to set.
	 */
	public static function setInstance($instance) 
	{
		self::$_instances[get_class($instance)] = $instance;
	}

	/**********************************************
	 * Logging Functions
	 ***********************************************/
	/**
	 * @param $message
	 * @param $args
	 * @return unknown_type
	 */
	protected function _logInfo($message, $args = null) 
	{
		$this->_logMessage(LOG_LEVEL_INFO, $message, $args);
	}
	
	/**
	 * @param $message
	  * @param $args
	 * @return unknown_type
	 */
	protected function _logDebug($message, $args = null) 
	{
		$this->_logMessage(LOG_LEVEL_DEBUG, $message, $args);
	}

	/**
	 * @param $message
	 * @param $args
	 * @return unknown_type
	 */
	protected function _logError($message, $args = null) 
	{
		$this->_logMessage(LOG_LEVEL_ERROR, $message, $args);
	}
	
	/**
	 * @param $message
	 * @param $args
	 * @return unknown_type
	 */
	protected function _logWarning($message, $args = null) 
	{
		$this->_logMessage(LOG_LEVEL_WARN, $message, $args);
	}
	
	/**
	 * @param $message
	 * @param $args
	 * @return unknown_type
	 */
	protected function _logFatal($message, $args = null) 
	{
		$this->_logMessage(LOG_LEVEL_FATAL, $message, $args);
	}
	
	/**
	 * @param $logLevel
	 * @param $message
	 * @param $other_args
	 * @return unknown_type
	 */
	protected function _logMessage($logLevel, $message, $other_args = null) 
	{
		$className = get_called_class();
		$timestamp = date("r");
		$message = "$timestamp $className: $message";
		$args = array($logLevel, $message, $other_args);
		$this->dispatch('EventLogged', $args);
	}
	
	protected function _log($message, $other_args = null) 
	{
		$this->_logInfo($message, $other_args);
	}
}
