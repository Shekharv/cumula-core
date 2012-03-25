<?php
namespace Cumula\Components\DataStoreWebAPI;

class DataStoreWebAPI extends \Cumula\Base\Component {
	protected $_models;
	
	public function __construct() {
		parent::__construct();
		
		$this->addEvent('GatherDataStores');
	}
	
	public function startup() {
		$prefix = $this->getConfigValue('prefix', '/api');
		A('Router')->bind('GatherRoutes', 
			array(
				$prefix.'/$type/create' => this('create'),
				$prefix.'/$type/update/$id' => this('update'),
				$prefix.'/$type/delete/$id' => this('destroy'),
				$prefix.'/$type/load/$id' => this('load'),
				$prefix.'/$type/query' => this('query'),
				$prefix.'/$type/findByAnyFilter' => this('findByAnyFilter'),
			)
		);
		
		A('Application')->bind('BootPrepare', array($this, 'gather'));
	}
	
	public function gather() {
		$models = array();
		$this->dispatch('GatherDataStores', array(), function($return) use (&$models) {
			$models = array_merge($models, $return);
		});
		
		foreach($models as $model => $ds) {
			$this->_processModel($model, $ds);
		}
	}
	
	protected function _processModel($model, $ds) {
		$this->_models[strtolower($model)] = $ds;
	}
	
	public function create($route, $router, $args) {
		if(!$this->_checkArgs($args))
			$this->render404();
		
		$ds = $this->_models[strtolower($args['type'])];
		$ds->connect();
		if($ds->create((object)$args)) {
			$this->_returnResult($this->load(null, null, array('id' => $ds->lastRowId())));
		} else {
			$this->_returnFalse();
		}
	}
	
	public function load($route, $router, $args) {
		if(!$this->_checkArgs($args))
			$this->render404();
		
		$ds = $this->_models[strtolower($args['type'])];
		$ds->connect();
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
		
		$ds = $this->_models[strtolower($args['type'])];
		$ds->connect();
		$ds->update((object)$args) ? $this->_returnTrue() : $this->_returnFalse();
	}
	
	public function destroy($route, $router, $args) {
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->_models[strtolower($args['type'])];
		$ds->connect();
		$ds->destroy((object)$args) ? $this->_returnTrue() : $this->_returnFalse();
	}
	
	public function query($route, $router, $args) {
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->_models[strtolower($args['type'])];
		unset($args['type']);
		$ds->connect();
		$this->_returnResult($ds->query($args));
	}
	
	public function findByAnyFilter($route, $router, $args) {
		$order = null;
		$start = 0;
		$limit = 10;
		$data = array();
		if(!$this->_checkArgs($args) && isset($args['id']))
			$this->render404();
		
		$ds = $this->_models[strtolower($args['type'])];
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
		$ds->connect();
		$this->_returnResult($ds->findByAnyFilter($args, $limit, $start, $order, $data));
	}
	
	protected function _checkArgs($args) {
		return (isset($args) && 
				isset($args['type']) && 
				in_array(strtolower($args['type']), array_keys($this->_models)));
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