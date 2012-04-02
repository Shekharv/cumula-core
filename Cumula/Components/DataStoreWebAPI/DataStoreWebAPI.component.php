<?php
namespace Cumula\Components\DataStoreWebAPI;

class DataStoreWebAPI extends \Cumula\Application\SimpleComponent {

	public $defaultConfig = array(
		'basePath' => '/api'
		);
	
	public $routes = array(
		'/$type/create' => 'create',
		'/$type/update/$id' => 'update',
		'/$type/delete/$id' => 'destroy',
		'/$type/load/$id' => 'load',
		'/$type/query' => 'query',
		'/$type/findByAnyFilter' => 'findByAnyFilter'
		);
	
	public $events = array(
		'GatherDataStores'
		);

		
	public function startup() {
		parent::startup();
		A('Application')->bind('BootPrepare', array($this, 'gather'));
	}
	
	public function gather() {
		$models = array();
		$this->dispatch('GatherDataStores', array(), function($return) use (&$models) {
			$models = array_merge($models, $return);
		});
		
		foreach($models as $model => $ds) {
			$this->dataStores[strtolower($model)] = $ds;
		}
	}
	
	public function create($route, $router, $args) {
		if(!$this->_checkArgs($args))
			$this->render404();
		
		$ds = $this->dataStores[strtolower($args['type'])];
		if($ds->create((object)$args)) {
			$this->_returnResult($this->load(null, null, array('id' => $ds->lastRowId())));
		} else {
			$this->_returnFalse();
		}
	}
	
	public function load($route, $router, $args) {
		if(!$this->_checkArgs($args))
			$this->render404();
		
		$ds = $this->dataStores[strtolower($args['type'])];
		$r = $ds->query(array($ds->getSchema()->getIdField() => $args['id']));
		if($r && !empty($r)) {
			$this->_returnResult($r);
		} else {
			$this->_returnFalse();
		}
	}
	
	public function update($route, $router, $args) {
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->dataStores[strtolower($args['type'])];
		$ds->update((object)$args) ? $this->_returnTrue() : $this->_returnFalse();
	}
	
	public function destroy($route, $router, $args) {
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->dataStores[strtolower($args['type'])];
		$ds->destroy((object)$args) ? $this->_returnTrue() : $this->_returnFalse();
	}
	
	public function query($route, $router, $args) {
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->dataStores[strtolower($args['type'])];
		unset($args['type']);
		$this->_returnResult($ds->query($args));
	}
	
	public function findByAnyFilter($route, $router, $args) {
		$order = null;
		$start = 0;
		$limit = 10;
		$data = array();
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->dataStores[strtolower($args['type'])];
		unset($args['type']);
		if(isset($args['order'])) {
			$order = $args['order'];
			unset($args['order']);
		}
		if(isset($args['start'])) {
			$start = $args['start'];
			unset($args['start']);
		}
		if(isset($args['limit'])) {
			$limit = $args['limit'];
			unset($args['limit']);
		}
		if(isset($args['data'])) {
			$data = $args['data'];
			unset($args['data']);
		}
		$this->_returnResult($ds->findByAnyFilter($args, $limit, $start, $order, $data));
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