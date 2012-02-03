<?php
namespace Cumula;
use Templater\Templater as Templater;
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
class Router extends BaseComponent 
{

	// Stores all routes registered with the application
	protected $_collectedRoutes = array();

	public function __construct() 
	{
		parent::__construct();

		$this->_routes = array();
		$this->addEvent('router_collect_routes');
		$this->addEvent('router_file_not_found');
		$this->addEvent('router_add_route');

		$this->addEventListenerTo('Application', 'boot_preprocess', array(&$this, 'collectRoutes'));
		$this->addEventListenerTo('Application', 'boot_process', array(&$this, 'processRoute'));
		$this->addEventListener('router_file_not_found', array(&$this, 'filenotfound'));
	}

	public function filenotfound($event, $dispatcher, $request, $response) 
	{
		//TODO: do something more smart here
		$fileName = Templater::instance()->config->getConfigValue('template_directory', TEMPLATEROOT).'404.tpl.php';
		$this->render($fileName);
		$response->response['content'] = $this->renderPartial(implode(DIRECTORY_SEPARATOR, array(APPROOT, 'public', '404.html')));
		$response->send404();
	}

	public function addRoutes($routes) 
	{
		if(is_array($routes))
		{
			$this->_collectedRoutes = array_merge($this->_collectedRoutes, $routes);
		}
	}
	
	public function setRoutes($routes) 
	{
		$this->_collectedRoutes = $routes;
	}
	
	public function getRoutes() 
	{
		return $this->_collectedRoutes;
	}

	public function collectRoutes($event) 
	{
		$this->dispatch('router_collect_routes', array(), 'addRoutes');
		$routes = $this->_collectedRoutes;

		if (!$routes)
		{
			return;
		}

		foreach ($routes as $route => $return) 
		{
			if (is_array($return[0])) 
			{
				$handler = $return[0];
				$args = !empty($return[1]) ? $return[1] : array();
			} 
			else 
			{
				$handler = $return;
				$args = array();
			}
			$this->dispatch('router_add_route', array($route, $handler, $args));
			$this->_addRoute($route, $handler);
		}
	}

	public function processRoute($event, $dispatcher, $request, $response) 
	{
		$routes = $this->_parseRoute($request);
		if (!count($routes)) 
		{
			$this->dispatch('router_file_not_found', array($request, $response));
		}

		foreach ($routes as $route => $args) 
		{
			$args = array_merge($request->params, $args);
			$this->dispatch($route, array($args, $request, $response));
		}
	}

	protected function _parseRoute($request) 
	{
		//The return array of matching handlers
		$return_handlers = array();

		//Trim off forward slash
		$path = substr($request->path, 1, strlen($request->path));

		//Trim off trailing slash
		if(substr($path, strlen($path)-1, strlen($path)) == '/')
		{
			$path = substr($path, 0, strlen($path)-1);
		}
			
		//Generate array of url segments
		$segments = explode('/', $path);
		//Iterate through passed routes
		foreach ($this->getEvents() as $route => $handlers) 
		{
			if ($route == '/' && ($path == '/' || $path == '')) 
			{
				$return_handlers[$route] = array();
				return $return_handlers;
			}
			
			//Check if the event is a route, if not continue
			if (substr($route, 0, 1) != '/')
			{
				continue;
			}

			//Extract route segemtns
			$route_segments = explode('/', substr($route, 1, strlen($route)));
			$match = false;
			$args = array();

			if (count($segments) != count($route_segments))
			{
				continue;
			}

			//Iterate through all URL segments
			foreach ($segments as $i => $segment)
			{
				$route_segment = $i < count($route_segments) ? $route_segments[$i] : false;

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
				} 
				else if ($route_segment == $segment) 
				{
					//Route segment and segment match, go to next iterator
					$match = true;
				} 
				else 
				{
					$match = false;
					break;
				}

				//If route is wildcard the rest of the url will match
				if($route_segment == '*') 
				{
					$match = true;
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
		$this->addEventListener($route, $handler);
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
