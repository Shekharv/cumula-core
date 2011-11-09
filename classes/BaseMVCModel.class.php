<?php
namespace Cumula;

abstract class BaseMVCModel extends EventDispatcher {
	protected static $_fields;
	protected $_data;
	protected static $_dataStore = array();
	protected $_fieldsToSerialize = array();
	protected $exists;
	
	/**
	 * Set Up the Data Store
	 * @param void
	 * @return void
	 **/
	public static function setupDataStore() 
	{
		throw new \Exception(sprintf('%s needs to implement setupDataStore() itself', get_class($this)));
	} // end function setupDataStore
	
	/**
	 * Set up the Fields for the Data Store
	 * @param void
	 * @return void
	 **/
	public static function setupFields() 
	{
		throw new \Exception(sprintf('%s needs to implement setupFields() itself', get_class($this)));
	} // end function setupFields
	
	public function __construct($args = array(), $exists = false) {
		parent::__construct();
		if(!is_array($args)) {
			$args = (array)$args;
		}
		$fields = static::getFields();
		foreach($fields as $field => $data) {
			if(isset($args[$field]))
				$this->_data[$field] = $args[$field];
		}
		$this->exists = $exists;
	}
	
	public function serialize($fields) {
		if(is_array($fields))
			$this->_fieldsToSerialize = $fields;
		else if(is_string($fields))
			$this->_fieldsToSerialize[] = $fields;
	}
	
	public static function find($args, $order = array(), $limit = null) {
		$res = static::getDataStore()->query($args, $order, $limit);
		$class = get_called_class();
		if($res && is_array($res)) {
			if(count($res) > 1) {
				for($i = 0; $i < count($res); $i++) {
					$res[$i] = new $class($res[$i], true);
				}
			} else {
				$res = new $class($res[0], true);
			}
			return $res;
		} else {
			return false;
		}	
	}
  
  
  public static function findOne($args) {
		$res = static::getDataStore()->query($args, null, 1);
		$class = get_called_class();
		if($res && is_array($res)) {
			for($i = 0; $i < count($res); $i++) {
				$res[$i] = new $class($res[$i], true);
			}
			return $res[0];
		} else {
			return false;
		}	
	}
  
	
	public static function findAll() {
		$res = static::find(array());
		if(!is_array($res))
			$res = array($res);
		return $res;
	}
	
	public static function getDataStore() {
		$class = get_called_class();
		if(!isset(self::$_dataStore[$class]))
			self::$_dataStore[$class] = static::setupDataStore();
		
		return self::$_dataStore[$class];
	}
	
	public static function addField($fieldName, $type, $args = array()) {
		$class = get_called_class();
		$args['type'] = $type;
		if(!isset(self::$_fields[$class]))
			self::$_fields[$class] = array();
		self::$_fields[$class][$fieldName] = $args;
	}
	
	public static function getFields() {
		static::setupFields();
		$class = get_called_class();
		return self::$_fields[$class];
	}
	
	public function save() {
		if($this->exists)
			return $this->update();
		else
			return $this->create();
	}
	
	public function destroy() {
		$res = static::getDataStore()->destroy($this->rawObject());
		if($res)
			$this->exists = false;
		return $res;
	}
	
	public function create() {
		$res = static::getDataStore()->create($this->rawObject());
		if($res) {
			$id = static::getSchema()->getIdField();
			$this->$id = static::getDataStore()->lastRowId();
			$this->exists = true;
		}
		return $res;
	}
	
	public function update() {
		$this->_log('MVCModel::update called');
		return static::getDataStore()->update($this->rawObject());
	}
	
	public function updateValues($vals) {
		foreach($vals as $key => $value) {
			$this->_data[$key] = $value;
		}
	}
	
	public static function getSchema() {
		//implemented by children classes
	}
	
	public function __get($name) {
		if(isset($this->_data[$name])) {
			$val = $this->_data[$name];
			if(in_array($name, $this->_fieldsToSerialize) && is_string($val)) {
				return unserialize($val);
			}
			return $val;
		}
	}
	
	public function __isset($name) {
		return isset($this->_data[$name]);
	}
	
	public function __set($name, $value) {	
		$this->_data[$name] = $value;
	}
	
	public function __unset($name) {
		unset($this->_data[$name]);
	}
	
	public function rawObject() {
		$obj = new \stdClass();
		foreach($this->_data as $key => $value) {
			if(in_array($key, $this->_fieldsToSerialize)){
				$value = serialize($value);
			}
			$obj->$key = $value;
		}
		return $obj;
	}
}
