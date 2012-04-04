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
 * Router Component
 *
 * An interface for routing.  Provides an API for specifying routes and handlers, built on top of the 
 * EventDispatch system.
 *
 * @package		Cumula
 * @subpackage	Router
 * @author     Seabourne Consulting
 */
class Router extends \Cumula\Base\Component
{

	// Stores all routes registered with the application
	protected $_collectedRoutes = array();
	protected $_routeConfigs;
	protected $_routeTypes;

	public function __construct() 
	{
		parent::__construct();

		$this->_routes = array();
		$this->_routeConfigs = array();
		$this->addEvent('GatherRoutes');
		$this->addEvent('RouterFileNotFound');
		$this->addEvent('RouterAddRoute');
		$this->addEvent('GatherRouteTypes');

		A('Application')->bind('BootPreprocess', array($this, 'collectRouteTypes'));
		A('Application')->bind('BootPreprocess', array($this, 'collectRoutes'));
		A('Application')->bind('BootProcess', array($this, 'processRoute'));
		$this->bind('RouterFileNotFound', array($this, 'fileNotFound'));
	}

	public function fileNotFound($event, $dispatcher, $request, $response) 
	{
		$this->renderNotFound();
	}
	
	public function getRoutes() 
	{
		$routes = array();
		foreach($this->getEvents() as $route => $handler) {
			if(substr($route, 0, 1) == '/' || substr($route, 0, 1) == '>')
				$routes[] = $route;
		}
		return $routes;
	}

	public function collectRoutes($event) 
	{
		$routes = array();
		$this->dispatch('GatherRoutes', array(), function($route) use (&$routes) {
			if(is_array($route))
				$routes = array_merge($routes, $route);
		});
		$this->_collectedRoutes = $routes;
		
		if (!$routes)
		{
			return;
		}

		foreach ($routes as $route => $return) 
		{
			if (is_array($return) && isset($return['callback']))
			{
				$handler = $return['callback'];
				unset($return['callback']);
				$config = $return;
			} 
			else 
			{
				$handler = $return;
				$config = array();
			}
			$this->setRouteConfig($route, $config);
			$this->dispatch('RouterAddRoute', array($route, $handler, $config));
			$this->_addRoute($route, $handler);
		}
	}
	
	public function collectRouteTypes() {
		$routeTypes = array();
		$this->dispatch('GatherRouteTypes', function($routeType) use (&$routeTypes) {
			$routeTypes = array_merge($routeTypes, $routeType);
		});
		$this->_routeTypes = $routeTypes;
	}
	
	public function getRouteTypes() {
		return $this->_routeTypes;
	}
	
	public function getRouteConfig($route) {
		return isset($this->_routeConfigs[$route]) ? $this->_routeConfigs[$route] : false;
	}
	
	public function setRouteConfig($route, $config) {
		$this->_routeConfigs[$route] = $config;
	}

	public function processRoute($event, $dispatcher, $request, $response) 
	{
		$routes = $this->parseRoute($request->path);
		if (!count($routes)) 
		{
			$this->dispatch('RouterFileNotFound', array($request, $response));
		}

		foreach ($routes as $route => $args) 
		{
			$args = array_merge($request->params, $args);
			$this->dispatch($route, array($args, $request, $response));
		}
	}

	public function parseRoute($origPath) 
	{
		//The return array of matching handlers
		$return_handlers = array();

		foreach($this->_routeTypes as $routeType => $separator) {
			if(substr($origPath, 0, strlen($routeType)) == $routeType) 
				break;
		}
		
		//Trim off route type indicator
		$path = substr($origPath, strlen($routeType), strlen($origPath)-1);
		
		//Trim off trailing slash
		if(substr($path, strlen($path)-1, strlen($path)) == '/')
		{
			$path = substr($path, 0, strlen($path)-1);
		}
			
		//Generate array of url segments
		$segments = explode($separator, $path);
		//Iterate through passed routes
		foreach ($this->getEvents() as $route => $handlers) 
		{
			if (($route == $routeType && $origPath == $routeType)) 
			{
				$return_handlers[$route] = array();
				return $return_handlers;
			}
			
			//Check if the event is a route, if not continue
			if (substr($route, 0, 1) != $routeType)
			{
				continue;
			}
		
			//Extract route segemtns
			$route_segments = explode($separator, substr($route, 1, strlen($route)));
			$match = false;
			$args = array();

			if ((count($segments) != count($route_segments) && !strstr($route, '*')))
			{
				continue;
			}

			//Iterate through all URL segments
			foreach ($segments as $i => $segment)
			{
				$route_segment = $i < count($route_segments) ? $route_segments[$i] : false;
				//If route is wildcard the rest of the url will match
				if($route_segment == '*') 
				{
					$match = true;
					break;
				}

				//If the route is shorter than the url, go to next route
				if(!$route_segment) 
				{
					$match = false;
					break;
				}

				//Route segment is a variable, save for parsing
				if (substr($route_segment, 0, 1) == '$') 
				{
					$args[substr($route_segment, 1, strlen($route_segment))] = $segment;
					$match = true;
					continue;
				} 
				else if ($route_segment == $segment) 
				{
					//Route segment and segment match, go to next iterator
					$match = true;
					continue;
				} 
				else 
				{
					$match = false;
					break;
				}
			}

			//The urls match, so we call the passed handler function, passing in the args
			if ($match) 
			{
				$args = array_merge(Request::instance()->params, $args);
				$return_handlers[$route] = $args;
			}
		}
		return $return_handlers;
	}

	protected function _addRoute($route, $handler) 
	{
		$this->bind($route, $handler);
	}

  /**
   * Implmentation of getInfo method
   * @param void
   * @return array
   **/
	public static function getInfo() 
	{
		return array(
			'name' => 'Path Router',
			'description' => 'Used to manage the URL Paths that are passed to the Cumula Framework',
			'dependencies' => array(),
			'version' => '0.1.0',
		);
	} // end function getInfo
}
