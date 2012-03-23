<?php
namespace Cumula\Application;

class SimpleComponent extends \Cumula\Base\Component {
	public $dataStores;
    
	public function startup() {
		parent::startup();
		$this->startDataStores();
		$this->registerRoutes();
	}

	public function shutdown() {
		parent::shutdown();
		$this->stopDataStores();
	}

	public function registerRoutes() {
		if (!property_exists($this, 'routes')) {
			return;
		}
		$hasRouteSetup = method_exists($this, 'routeSetup');
		$hasRouteTeardown = method_exists($this, 'routeTeardown');
		$basePath = $this->getConfigValue('basePath', '');
		$routes = array();
		$router = A('Router');
		foreach($this->routes as $route => $method) {
			$full_route = $basePath.$route;
			$routes[$full_route] = array($this, $method);
			if ($hasRouteSetup) {
				$router->bind('Before'.$full_route, array($this, 'routeSetup'));
			}
			if ($hasRouteTeardown) {
				$router->bind('After'.$full_route, array($this, 'routeTeardown'));
			}
		}
		$router->bind('GatherRoutes', $routes);
	}

	public function routeSetup() {
		$this->connectDataStores();
	}
	
	public function startDataStores() {
		$this->dataStores = array();
		foreach($this->getConfigValue('dataStores', array()) as $name => $params) {
			if (array_key_exists('factory', $params)) {
				$factory = $params['factory'];
				unset($params['factory']);
				$ds = A($factory)->get();
			} else {
				$engine = $params['engine'];
				unset($params['engine']);
				$ds = new $engine();
			}
			$ds->setup($this->schemas[$name], 'id', $name, $params);
			$this->dataStores[$name] = $ds;
		}
	}
	public function connectDataStores() {
		if (!$this->dataStores) {
			return;
		}
		foreach($this->dataStores as $name => $ds) {
			$ds->connect();
		}
	}
	
	public function stopDataStores() {
		if (!$this->dataStores) {
			return;
		}
		foreach($this->dataStores as $name => $ds) {
			$ds->disconnect();
		}
	}
	
}
