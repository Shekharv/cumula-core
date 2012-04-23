<?php
namespace Cumula\Application;

class SimpleComponent extends \Cumula\Base\Component {
	public $dataProviders = array();
    
	public function startup() {
		parent::startup();
		$this->registerEvents();
		$this->startDataStores();
		$this->registerRoutes();
	}

	public function shutdown() {
		parent::shutdown();
		$this->stopDataProviders();
	}

	public function registerEvents() {
		if (!property_exists($this, 'events')) {
			return;
		}
		foreach($this->events as $event) {
			$this->addEvent($event);
		}
	}
	
	public function registerRoutes() {
		if (!property_exists($this, 'routes')) {
			return;
		}
		$hasRouteStartup = method_exists($this, 'routeStartup');
		$hasRouteShutdown = method_exists($this, 'routeShutdown');
		$basePath = $this->getConfigValue('basePath', '');
		if (!strpos($basePath, '/')) { // NOTE: want False OR 0
			$basePath = '/' . $basePath;
		}
		$routes = array();
		$router = A('Router');
		foreach($this->routes as $route => $method) {
			if (!strpos($route, '/')) { // NOTE: want False OR 0
				$route = '/' . $route;
			}
			$full_route = $basePath.$route;
			$routes[$full_route] = array($this, $method);
			if ($hasRouteStartup) {
				$router->bind('Before'.$full_route, array($this, 'routeStartup'));
			}
			if ($hasRouteShutdown) {
				$router->bind('After'.$full_route, array($this, 'routeShutdown'));
			}
		}
		$router->bind('GatherRoutes', $routes);
	}

	public function routeStartup() {
		$this->connectDataProviders();
	}
	
	public function startDataStores($config_key='dataProviders') {
		foreach($this->getConfigValue($config_key, array()) as $name => $params) {
			if (is_null($params)) {
				continue;
			}
			if (isset($params['engine'])) {
				$engine = $params['engine'];
				unset($params['engine']);
				$config = isset($params['config']) ? $params['config'] : $params;
				$ds = new $engine($config);
			}
			$this->dataProviders[$name] = $ds;
		}
	}
	
	public function connectDataProviders() {
		if (!$this->dataProviders) {
			return;
		}
		foreach($this->dataProviders as $name => $ds) {
			$ds->connect();
		}
	}
	
	public function stopDataProviders() {
		if (!$this->dataProviders) {
			return;
		}
		foreach($this->dataProviders as $name => $ds) {
			$ds->disconnect();
		}
	}
	
}
