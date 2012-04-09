<?php
namespace Cumula\Components\DataProviderWebAPI;

class DataProviderWebAPI extends \Cumula\Application\SimpleComponent {

	public $defaultConfig = array(
		'basePath' => '/api'
		);
	
	public $routes = array(
		'/$type/$method' => 'process',
		'/$type/$method/$id' => 'process'
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
		$type = $args['type'];
		$ref = new \ReflectionClass($this->dataStores[strtolower($type)]);
		if(!method_exists($this->dataStores[strtolower($type)], $args['method'])) 
			return $this->renderNotFound();
		$method = $ref->getMethod($args['method']);
		
		unset($args['method']);
		unset($args['type']);
		
		$params = array();
		foreach($method->getParameters() as $param) {
			if(isset($args[$param->name])) {
				$params[] = $args[$param->name];
				unset($args[$param->name]);
			} else if ($param->isDefaultValueAvailable()){
				$params[] = $param->getDefaultValue();
			}
		}
		if(!empty($args))
			array_unshift($params, $args);
			
		try{
			$ret = $method->invokeArgs($this->dataStores[strtolower($type)], $params);
		} catch (\Exception $e) {
			return $this->renderNotFound();
		}
		
		$this->_returnResult($ret);
	}
	
	protected function _checkArgs($args) {
		return (isset($args) && 
				isset($args['type']) && 
				in_array(strtolower($args['type']), array_keys($this->dataStores)));
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
}