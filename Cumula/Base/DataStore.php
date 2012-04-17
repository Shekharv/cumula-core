<?php
namespace Cumula\Base;
use \Cumula\Schema\Simple as SimpleSchema;

/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */


/**
 * BaseDataStore Class
 *
 * Abstract base class for all DataStores.  This class handles the datastore schema installation.
 * 
 * Each datastore must have a schema that describes the fields used by the data object.  Most 
 * importantly, the schema describes the field used as the id for each record in the datastore.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
abstract class DataStore extends \Cumula\Application\EventDispatcher {
	protected $_connected = false;
	protected $_config = array();
	protected $_fields = false;
	
	public function requiredConfig() {
		return array('fields', 'idField');
	} 

	/**
	 * Constants
	 */
	const FIELD_TYPE_STRING = 'string';
	const FIELD_TYPE_INTEGER = 'integer';
	const FIELD_TYPE_FLOAT = 'float';
	const FIELD_TYPE_BOOL = 'boolean';
	const FIELD_TYPE_TEXT = 'text';
	const FIELD_TYPE_DATETIME = 'datetime';
	const FIELD_TYPE_BLOB = 'blob';

	/**
	 * Constructor
	 * 
	 * @return unknown_type
	 */
	public function __construct($config) {
		parent::__construct();
		$this->_config = $config;
		$keys = $this->requiredConfig();
		foreach($keys as $key) {
			if(!isset($config[$key])) 
				throw new \Exception("Must provide a '$key' config value for ".get_called_class()." got ". array_keys($config));
		}

		$this->_fields = $config['fields'];
		
		$this->addEvent('Load');
		$this->addEvent('Save');
		
	}
	
	public function isConnected() {
		return $this->_connected;
	}

	public function prepareSave($obj) {
		$this->dispatch('Save', array($obj), function($new_obj) use (&$obj) {
				if ($new_obj) {
					$obj = $new_obj;
				}
			});
		return $obj;
	}

	public function prepareLoad($obj) {
		$this->dispatch('Load', array($obj), function($new_obj) use (&$obj) {
				if ($new_obj) {
					$obj = $new_obj;
				}
			});
		return $obj;
	}
	
	public function create($obj) {
		throw new \Exception('Create is not implemented by this DataStore.');
	}
	
	public function update($obj) {
		throw new \Exception('Update is not implemented by this DataStore.');
	}
	
	public function destroy($obj) {
		throw new \Exception('Destroy is not implemented by this DataStore.');
	}
	
	public function get($args) {
		throw new \Exception('Get is not implemented by this DataStore.');
	}
	
	public function findByFullText($query, $order = null, $limit = null, $start = null, $data = null) {
		throw new \Exception('FindByFullText is not implemented by this DataStore.');
	}
	
	public function findRecent($order = null, $limit = null, $start = null, $recentField = null, $pullFrom = null, $data = null) {
		throw new \Exception('findRecent is not implemented by this DataStore.');
	}
	
	public function findByAnyFilter($filters, $order = null, $limit = null, $start = null, $data = null) {
		throw new \Exception('findByAnyFilter is not implemented by this DataStore.');
	}
	
	public function findByAllFilters($filters, $order = null, $limit = null, $start = null, $data = null) {
		throw new \Exception('findByAllFilters is not implemented by this DataStore.');
	}
	
	public function lastObjId() {
		throw new \Exception('LastObjId is not implemented by this DataStore.');
	}
	
	public function translateFields($fields) {
		return $fields;
	}
	
	public function install() {
		return true;
	}
	
	public function uninstall() {
		return true;
	}
	
	abstract public function connect();
	
	abstract public function disconnect();
	
	abstract public function recordExists($id);
	
	public function newObj($fields = null) {
		$obj = new \stdClass();
		foreach($this->_fields as $key => $value) {
			if(isset($config['fieldMapping']) && isset($config['fieldMapping'][$key]) && is_callable($config['fieldMapping'][$key])) {
				$val = call_user_func_array($config['fieldMapping'][$key], array($fields[$key]));
			} else
				$val = $fields[$key];
			$obj->$key = $val;
		}
		
		return $obj;
	}	
	
	/**
	 * Returns the field used as the unique id for records
	 * @return unknown_type
	 */
	protected function _getIdField() {
		return $this->_config['idField'];
	}
	
	protected function _getNonIdFields() {
		$idField = $this->_getIdField();
		$ret = array();
		foreach($this->_fields as $key => $value) {
			if($key != $idField)
				$ret[] = $key;
		}
		return $ret;
	}
	
	/**
	 * Converts an object to an array of key/value pairs
	 * 
	 * @param $obj
	 * @return unknown_type
	 */
	protected function _objToArray($obj) {
		if(is_array($obj))
			return $obj;
		else
			return (array)$obj;
	}	
	
	protected function _arrayToObj($array) {
		return (object)$array;
	}
	
	/**
	 * Converts an array to a string.
	 * 
	 * @param array $arr
	 * @return unknown_type
	 */
	protected function _arrayToString(array $arr) {
		return implode(" ", $arr);
	}
	
	protected function _getIdValue($obj) {
		$idField = $this->_getIdField();
		return $obj->$idField;	
	}
	
	protected function _getNonIdValues($obj) {
		$idField = $this->_getIdField();
		$ret = array();
		foreach((array)$obj as $key => $value) {
			if($key != $idField)
				$ret[$key] = $value;
		}
		return $ret;
	}
	
	protected function _resultsObject($results, $total, $start, $numItems, $data = null) {
		return array('totalItems' => $total, 'start' => $start, 'numItems' => $numItems, 'results' => $results, 'data' => $data);
	}
}
