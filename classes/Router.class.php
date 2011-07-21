<?php
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
class Router extends BaseComponent {
	private $_routes;

	protected $_collectedRoutes = array();

	private $_routeStorage;

	public function __construct() {
		parent::__construct();

		$this->_routes = array();
		$this->addEvent(ROUTER_COLLECT_ROUTES);
		$this->addEvent(ROUTER_FILE_NOT_FOUND);
		$this->addEvent(ROUTER_ADD_ROUTE);

		Application::getInstance()->addEventListener(BOOT_PREPROCESS, array(&$this, 'collectRoutes'));
		Application::getInstance()->addEventListener(BOOT_PROCESS, array(&$this, 'processRoute'));
		$this->addEventListener(ROUTER_FILE_NOT_FOUND, array(&$this, 'filenotfound'));

		//$this->_routeStorage = new YAMLDataStore(array('source_directory' => dirname(__FILE__), 'filename' => 'routes.yml'));
	}

	public function filenotfound($event, $dispatcher, $request, $response) {
		//TODO: do something more smart here
		$fileName = Application::getTemplater()->config->getConfigValue('template_directory', TEMPLATEROOT).'404.tpl.php';
		$this->render($fileName);
		$response->response['content'] = $this->renderPartial(implode(DIRECTORY_SEPARATOR, array(APPROOT, 'public', '404.html')));
		$response->send404();
	}

	public function addRoutes($routes) {
		$this->_collectedRoutes = array_merge($this->_collectedRoutes, $routes);
	}

	public function collectRoutes($event) {
		$this->dispatch(ROUTER_COLLECT_ROUTES);
		$routes = $this->_collectedRoutes;
		if(!$routes)
			return;
		foreach($routes as $route => $return) {
			if(is_array($return[0])) {
				$handler = $return[0];
				$args = !empty($return[1]) ? $return[1] : array();
			} else {
				$handler = $return;
				$args = array();
			}
			$this->dispatch(ROUTER_ADD_ROUTE, array($route, $handler, $args));
			$this->_addRoute($route, $handler);
		}
	}

	public function processRoute($event, $dispatcher, $request, $response) {
		$routes = $this->_parseRoute($request);
		if(!count($routes)) {
			$this->dispatch(ROUTER_FILE_NOT_FOUND, array($request, $response));
		}
		foreach($routes as $route => $args) {
			$args = array_merge($_GET, $args);
			$this->dispatch($route, array($args, $request, $response));
		}
	}

	protected function _parseRoute($request) {
		//The return array of matching handlers
		$return_handlers = array();

		//Trim off forward slash
		$path = substr($request->path, 1, strlen($request->path));

		//Trim off trailing slash
		if(substr($path, strlen($path)-1, strlen($path)) == '/')
			$path = substr($path, 0, strlen($path)-1);
			
		//Generate array of url segments
		$segments = explode('/', $path);
		//Iterate through passed routes
		foreach($this->_eventTable as $route => $handlers) {
			if($route == '/' && ($path == '/' || $path == '')) {
				$return_handlers[$route] = array();
				return $return_handlers;
			}
			
			//Check if the event is a route, if not continue
			if(substr($route, 0, 1) != '/')
			continue;

			//Extract route segemtns
			$route_segments = explode('/', substr($route, 1, strlen($route)));
			$match = false;
			$args = array();

			if(count($segments) != count($route_segments))
			continue;

			//Iterate through all URL segments
			for($i = 0; $i < count($segments); $i++) {
				$segment = $segments[$i];
				$route_segment = $i < count($route_segments) ? $route_segments[$i] : false;

				//If the route is shorter than the url, go to next route
				if(!$route_segment) {
					$match = false;
					break;
				}



				//Route segment is a variable, save for parsing
				if(substr($route_segment, 0, 1) == '$') {
					$args[substr($route_segment, 1, strlen($route_segment))] = $segment;
					$match = true;
				} else if($route_segment == $segment) {
					//Route segment and segment match, go to next iterator
					$match = true;
				} else {
					$match = false;
					break;
				}

				//If route is wildcard the rest of the url will match
				if($route_segment == '*') {
					$match = true;
					break;
				}
			}

			//The urls match, so we call the passed handler function, passing in the args
			if($match) {
				$args = array_merge(Application::getRequest()->params, $args);
				$return_handlers[$route] = $args;
			}
		}
		return $return_handlers;
	}

	protected function _addRoute($route, $handler) {
		if(!$this->eventExists($route))
			$this->addEvent($route);
		$this->addEventListener($route, $handler);
	}
}
