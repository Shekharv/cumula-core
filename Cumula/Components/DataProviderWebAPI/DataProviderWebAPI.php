<?php
namespace Cumula\Components\DataProviderWebAPI;

class DataProviderWebAPI extends \Cumula\Application\SimpleComponent {

	public $defaultConfig = array(
		'basePath' => '/api'
		);
	
	public $routes = array(
		'/$__type/$__method' => 'process',
		'/$__type/$__method/$__id' => 'process'
		);
	
	public $events = array(
		'GatherProviders'
		);

		
	public function startup() {
		parent::startup();
		A('Application')->bind('BootPrepare', array($this, 'gather'));
	}
	
	public function gather() {
		$models = array();
		$this->dispatch('GatherProviders', array(), function($return) use (&$models) {
			$models = array_merge($models, $return);
		});
		
		foreach($models as $model => $ds) {
			$this->dataStores[strtolower($model)] = $ds;
		}
	}
	
	public function process($route, $router, $args) {
		$type = $args['__type'];
		$ds = $this->dataStores[strtolower($type)];
		if (!$ds->isConnected()) {
			$ds->connect();
		}
		$ref = new \ReflectionClass($ds);
		if(!method_exists($ds, $args['__method']))
			return $this->renderNotFound();
		$method = $ref->getMethod($args['__method']);
		
		unset($args['__method']);
		unset($args['__type']);

		$takesFilters = false;
		$params = array();
		foreach($method->getParameters() as $param) {
			if(isset($args[$param->name])) {
				$params[] = urldecode($args[$param->name]);
				unset($args[$param->name]);
			} else if ($param->isDefaultValueAvailable()){
				$new_param[] = $param->getDefaultValue();
			}
			if ($param->name == 'filters') {
				$takesFilters = array_slice($params, -1);
			}
		}
		if(array_key_exists('__id', $args)) {
			$val = $args['__id'];
			unset($args['__id']);
			$args[$ds->_getIdField()] = $val;
		}
		
		if(!empty($args)) {
			if ($takesFilters !== false) {
				$params['filters'] = array_merge($takesFilters, $args);
			} else {
				array_unshift($params, $args);
			}
		}
		try{
			$ret = $method->invokeArgs($ds, $params);
		} catch (\Exception $e) {
			return $this->_returnError($e->getMessage());
		}
		
		$this->_returnResult($ret);
	}
	
	protected function _returnTrue() {
		$this->renderJSON(
			array('success' => 'true')
		);
	}
	
	protected function _returnFalse() {
		$this->renderJSON(
			array('success' => 'false')
		);
	}
	
	protected function _returnResult($result) {
		$this->renderJSON(
			array('success' => 'true',
				'result' => $result
			)
		);
	}

	protected function _returnError($message) {
		$this->renderJSON(
			array('success' => 'error',
				'error' => $message
			)
		);
	}
}