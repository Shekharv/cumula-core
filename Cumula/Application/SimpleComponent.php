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
		$basePath = $this->getConfigValue('basePath', '');
		$routes = array();
		foreach($this->routes as $route => $method) {
			$routes[$basePath.$route] = array($this, $method);
		}
		A('Router')->bind('GatherRoutes', $routes);
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
			$ds->connect();
			$this->dataStores[$name] = $ds;
		}
	}

	public function stopDataStores() {
		foreach($this->dataStores as $name => $ds) {
			$ds->disconnect();
		}
	}
	
}
