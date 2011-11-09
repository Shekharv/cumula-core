<?php
namespace Cumula;

abstract class BaseSchema extends EventDispatcher implements CumulaSchema {
	protected $_fields;
	protected $_idField;
	protected $_name;
	
	public function __construct($fields = null, $idField = null, $name = null) {
		parent::__construct();
		$this->setFields($fields);
		$this->setIdField($idField);
		$this->setName($name);
	}
	
	public function setFields($fields) {
		$this->_fields = $fields;
	}
	
	public function getFields() {
		return $this->_fields;
	}
	
	public function setIdField($idField) {
		$this->_idField = $idField;
	}
	
	public function getIdField() {
		return $this->_idField;
	}
	
	public function setName($name) {
		$this->_name = $name;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getObjInstance() {
		$obj = new \stdClass();
		foreach($this->getFields() as $field => $type) {
			$obj->$field = null;
		}
		return $obj;
	}
	
}
