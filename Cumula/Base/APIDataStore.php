<?php
namespace Cumula\Base;

abstract class APIDataStore extends DataStore {
	protected $_path;
	protected $_service;
	
	public function __construct($config) {
		$config['fields'] = array();
		$config['idField'] = '';
		
		parent::__construct($config);
		
		$this->_service = new DataService($config);
	}
	
}

?>