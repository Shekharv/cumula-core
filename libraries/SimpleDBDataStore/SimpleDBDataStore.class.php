<?php
namespace SimpleDBDataStore;
use \Cumula\BaseDataStore as BaseDataStore;

require_once("lib/sdb.php");

class SimpleDBDataStore extends BaseDataStore {
	
	protected $_service;
	protected $_domain;
	
	protected $_accessKey;
	protected $_secretKey;
	
	public function __construct(\Cumula\BaseSchema $schema, array $config) {
		parent::__construct($schema, $config);
		if (!isset($config['access_key']) || !isset($config['secret_key']))
			throw new Exception("Must have an access key and a secret key and a domain.");
			
		$this->_accessKey = $config['access_key'];
		$this->_secretKey = $config['secret_key'];
	}
	
	protected function _setDomain() {
		$this->_domain = $this->_schema->getName();
		$domains = $this->_service->listDomains();
		$exists = false;
		foreach($domains as $d) {
			if ($d == $this->_domain)
				$exists = true;
		}
		if(!$exists) {
			$this->_service->createDomain($this->_domain);
		}		
	}
	
	public function create($obj) {
		$vals = array();
		foreach($this->_getNonIdValues($obj) as $key => $value) {
			$vals[$key] = array('value' => $value);
		}
		$obj->id = rand(time(), time()+10000000);
		$this->_service->putAttributes($this->_domain, $this->_getIdValue($obj), $vals);
	}
	
	public function update($obj) {
		$vals = array();
		foreach($this->_getNonIdValues($obj) as $key => $value) {
			$vals[$key] = array('value' => $value);
		}
		$this->_service->putAttributes($this->_domain, $this->_getIdValue($obj), $vals, null, true);
	}
	
	public function destroy($obj) {
		$this->_service->deleteAttributes($this->_domain, $this->_getIdValue($obj));
	}
	
	public function query($args = false, $order = array(), $limit = array()) {
		$statement = '';
		if(!$this->_service)
			$this->connect();
			
		if(is_array($args) || $args == false)	{
			$statement = "select * from ".$this->_domain;
			if(is_array($args) && count($args) > 0) {
				$statement .= " where ";
				foreach($args as $key => $value) {
					$statement .= "$key = '$value'";
				}
			} 
			$this->_logInfo("SimpleDB query statement is: ".$statement);
			$result = $this->_service->select('', $statement);
			$return = array();
			foreach($result as $r) {
				$obj = $this->newObj();
				foreach($r['Attributes'] as $key => $value) {
					$obj->$key = $value;
				}
				$obj->id = $r['Name'];
				$return[] = $obj;
			}
			return $return;
		} else if (is_string($args)) {
			$this->_logInfo('getting by id ', $args);
			//Assume $args is an id and lookup the attribute for the object directly
			$ret = $this->_service->getAttributes($this->_domain, $args);
			if($ret) {
				$this->_logInfo('simple db getAttributes return is', $ret);
				$ret['id'] = $args;
				return (object)$ret;
			} else {
				return false;
			}
		}
	}
	
	public function install() {
		
	}
	
	public function uninstall() {
		
	}
	
	public function translateFields($fields) {
		
	}
	
	public function recordExists($id) {
		
	}
	
	public function connect() {
		$this->_logInfo('connecting lib');
		if($this->_accessKey && $this->_secretKey) {
			$this->_service = new \SimpleDB($this->_accessKey, $this->_secretKey);
			$this->_setDomain();
			return true;
		} else {
			return false;
		}
	}
	
	public function disconnect() {
		
	}
	
	public function lastRowId() {
		
	}
	
}
