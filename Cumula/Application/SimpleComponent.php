<?php
namespace Cumula\Application;

class SimpleComponent extends \Cumula\Base\Component {
	public $dataProviders = array();
    
	public function startup() {
		parent::startup();
		$this->registerEvents();
		$this->dataProviders = $this->startDataProviders();
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
		$basePath = prefix_slash($basePath);
		$hasEndingSlash = (substr($basePath, -1) == '/');
		$routes = array();
		$router = A('Router');
		foreach($this->routes as $route => $method) {
			if (!is_string($route)) {
				$route = $method;
				$method = str_replace('-', '_', $method); 
			}
			if (!$hasEndingSlash) {
				$route = prefix_slash($route);
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
		$this->registerTemplate();
		$this->registerAssets('css');
		$this->registerAssets('js');
	}

	public function registerTemplate($config_key='template') {
		$template = $this->getConfigValue($config_key);
		if ($template) {
			A('AliasManager')->setAlias('Template', $template, false);
		}
	}

	public function registerAssets($type) {
		// TODO: this should allow some pattern config at least?
		$dir = implode(DIRECTORY_SEPARATOR, array($this->rootDirectory(), 'assets', $type));
		$files = array();
		
		foreach(glob($dir . DIRECTORY_SEPARATOR . '*.' . $type, GLOB_NOSORT) as $file) {
			$filename = basename($file);
			$files[] = '/assets/'.$this->componentName() . '/' . $type . '/' . $filename;
		}
		A('FileAggregator')->bind('Gather'.strtoupper($type).'Files', $files);
	}
	
	public function startDataProviders($config_key='dataProviders') {
		$ret = array();
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
			$ret[$name] = $ds;
		}
		return $ret;
	}
	
	public function connectDataProviders($providers=null) {
		if (!$providers) {
			if (!$this->dataProviders) {
				return;
			}
			$providers = $this->dataProviders;
		}
		foreach($providers as $name => $ds) {
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

	public function __get($name) {
		return $this->dataProviders[$name];
	}
	
}
