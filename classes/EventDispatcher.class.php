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
require_once 'Exception/EventException.class.php';
require_once(implode(DIRECTORY_SEPARATOR, array(
	dirname(__FILE__),
	'..',
	'includes',
	'core.inc',
)));

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
	protected static $eventHash = array();
	
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
		$calledClass = get_called_class();
		$eventHash = static::getEventHash();

		// See if the event exists.  If not, set it up in the eventHash
		if (!isset($eventHash[$calledClass][$event]))
		{
			$eventHash[$calledClass][$event] = array();
			static::setEventHash($eventHash);
		}
	}

	/**
	 * Removes an event from the registry table.
	 * 
	 * @param	string	The event to remove from the registry.
	 */
	public function removeEvent($event) 
	{
		$calledClass = get_called_class();
		$eventHash = static::getEventHash();
		if (isset($eventHash[$calledClass][$event])) 
		{
			unset($eventHash[$calledClass][$event]);
			static::setEventHash($eventHash);
		}
	}
	
	public function bind($event, $callback) {
		$myClass = get_class($this);
		$absClass = Autoloader::absoluteClassName($myClass);
		$myClass::addClassListenerHash($absClass, $event, $callback);
		$myClass::instance()->dispatch('event_registered', array($absClass, $event));
	}
	
	/**
	 * Add the Listener to the Class hash
	 * @param string $class The class the listener is being attached to
	 * @param string $event The Event the listener is for
	 * @param callback $callback The callback to be executed when the event is dispatched
	 * @return void
	 **/
	public static function addClassListenerHash($class, $event, $callback) 
	{
		$hash = static::getEventHash();
		
		if (!isset($hash[$class][$event]))
		{
			$hash[$class][$event] = array();
		}

		// Prevent a listener from being called twice.
		if (!in_array($callback, $hash[$class][$event]))
		{
			$hash[$class][$event][] = $callback;
		}
		
		static::setEventHash($hash);
	} // end function addListenerHash

	/**
	 * Determine whether an event exists in the hash or not
	 * @param string $eventName Event being checked
	 * @return mixed
	 **/
	public static function eventHashExists($eventName) 
	{
		$class = get_called_class();
		$eventHash = static::getEventHash();
		return isset($eventHash[$class][$eventName]) ? $eventHash[$class][$eventName] : FALSE;
	} // end function eventHashExists

	/**
	 * Given an event and handler, removes any matching entry in the event registry
	 * 
	 * @param	string The event to remove handler from.
	 * @param	function	a function, or an array containing the class and method, or a closure to remove.
	 */
	public function unbind($event, $handler) 
	{
		$calledClass = get_called_class();
		$eventHash = static::getEventHash();
		if (isset($eventHash[$calledClass][$event]))
		{
			foreach ($eventHash[$calledClass][$event] as $key => $listener)
			{
				if ($listener === $handler)
				{
						unset($eventHash[$calledClass][$event][$key]);
						static::setEventHash($eventHash);
						return;
				}
			}
		}
	}
	
	public function unbindAll($event) 
	{
		$calledClass = get_called_class();
		$eventHash = static::getEventHash();
		if (isset($eventHash[$calledClass][$event])) 
		{
			$eventHash[$calledClass][$event] = array();
		}
		static::setEventHash($eventHash);
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
		$class = get_called_class();
		$fireBeforeAndAfter = (stripos($event, 'Before') === FALSE) && (stripos($event, 'After') === FALSE);
		$beforeEvent = sprintf('Before%s', $event);
		$afterEvent = sprintf('After%s', $event);
		$hash = static::getEventHash();
		if(true)// (isset($hash[$class][$event]) && count($hash[$class][$event]) > 0)
		{
			//if $callback is a string, wrap it as a callable array with $this
			if (is_string($callback))
			{
				$callback = array($this, $callback);
			}
			
			array_unshift($data, $event, $this);

			global $level;
			
			// Flag to determine whether to dispatch the before and after events

			if ($event != 'EventDispatcherEventDispatched' && $fireBeforeAndAfter)
			{
				$level++;
			}
			
			if ($fireBeforeAndAfter && $this->getEventListeners($beforeEvent))
			{
				$new_data = array_slice($data, 2);
				$this->dispatch($beforeEvent, $new_data, $callback);
			}
			$hash = static::getEventHash();
			if(isset($hash[$class][$event]) && count($hash[$class][$event]) > 0) {
				$listeners = $hash[$class][$event];
				//For each listener call the handler function	
				foreach ($listeners as $event_handler) 
				{
					//Fire of an EVENT_DISPATCHED event if there are active listeners
					if ($event != 'EventDispatcherEventDispatched') 
					{	
						$new_data = array_slice($data, 2);
						$this->dispatch('EventDispatcherEventDispatched', array($event, $this, $event_handler, $level, $new_data));
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
				$data = array_slice($data, 2);
				$this->dispatch($afterEvent, $data, $callback);
			}

			if ($event != 'EventDispatcherEventDispatched' && $fireBeforeAndAfter)
			{
				$level--;
			}
			return true;
		} 
		else 
		{
			return false;
		}
	}
	 
	/**
	 * Get the events for the current class
	 * @param void
	 * @return mixed
	 **/
	public function getEvents() 
	{
		$class = __CLASS__;
		$calledClass = get_called_class();
		$eventHash = $class::getEventHash();
		return isset($eventHash[$calledClass]) ? $eventHash[$calledClass] : FALSE;
	} // end function getEvents
	
	public function eventIsRegistered($event) 
	{
		$class = __CLASS__;
		$calledClass = get_called_class();
		$eventHash = $class::getEventHash();
		return (isset($eventHash[$calledClass]) && isset($eventHash[$calledClass][$event]));
	}
	
	public function getEventListeners($event) 
	{
		$class = __CLASS__;
		$calledClass = get_called_class();
		$eventHash = $class::getEventHash();
		return (isset($eventHash[$calledClass]) && isset($eventHash[$calledClass][$event])) ? $eventHash[$calledClass][$event] : FALSE;
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
			//self::$_instances[$class] = new $class();
			return false;
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

	/**
	 * Getter for $this->eventHash
	 * @param void
	 * @return array
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	private static function getEventHash() 
	{
		return static::$eventHash;
	} // end function getEventHash()
	
	/**
	 * Setter for $this->eventHash
	 * @param array
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	private static function setEventHash(array $arg0) 
	{
		static::$eventHash = $arg0;
	} // end function setEventHash()
	
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
