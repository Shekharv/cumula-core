<?php
namespace Cumula\DataStore\YAML;
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

require_once dirname(__FILE__) . '/lib/sfYamlDumper.php';
require_once dirname(__FILE__) . '/lib/sfYamlParser.php';


/**
 * YAMLDataStore Class
 *
 * An implementation of DataStore using YAML.  A source directory and filename are passed in the config values and is used to save the
 * information in YAML format.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
class YAML extends \Cumula\Base\DataStore {
	private $_storage;
	private $_sourceDirectory;
	private $_filename;
	
	private $_cache;
	
	/**
	 * Accepts an array of config values as name => value pairs.  Two possible config values are:
	 *   -source_directory: the absolute file path to save the config file to
	 *   -filename: the YAML filename to save the information as
	 * 
	 * @param $config_values
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();
		$this->_storage = array();
	}
	 
	public function setup($fields, $id, $name, $configValues) {
		parent::setup($fields, $id, $name, $configValues);
		$this->setConfig($configValues);
	}

	public function setConfig($configValues) {
		$this->_sourceDirectory = $configValues['source_directory'];
		$this->_filename = $configValues['filename'];
	}
	
	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#connect()
	 */
	public function connect() {
		$this->_load();
	}
	
	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#disconnect()
	 */
	public function disconnect() {
		$this->_save();
	}
	
	public function create($obj) {
		$this->_createOrUpdate($obj);
	}
	
	protected function _createOrUpdate($obj) {
		$idField = $this->_schema->getIdField();
		$key = $this->_getIdValue($obj);
		//If object is a simple key/value (count == 2), set the value to be the remaining attribute, otherwise set the object as the value
		if(count((array)$obj) == 2) {
			foreach($obj as $k => $value) {
				if($k != $idField)
					$this->_storage[$key] = $value;
			}
		} else {
			$this->_storage[$key] = (array)$obj;
		}
		return $this->_save();
	}
	
	public function update($obj) {
		$this->_createOrUpdate($obj);
	}
	
	public function createOrUpdate($obj) {
		return $this->_createOrUpdate($obj);
	}
	
	public function destroy($obj) {
		if(is_string($obj)) {
			//if Obj is an ID (string), unset the entire record
			if ($this->recordExists($obj)) {
				unset($this->_storage[$obj]);
			}
		} else {
			//if obj is an object, unset the object based on the passed id
			$key = $this->_getIdValue($obj);
			unset($this->_storage[$key]);
			$this->_save();
		}
	}

	public function query($args, $order = null, $limit = null, $start = null) {
		$idField = $this->getSchema()->getIdField();
		if (is_array($args) && isset($args[$idField])) {
			$args = $args[$idField];
		}

		if ($this->recordExists($args)) {
			$obj = array($this->_storage[$args]);
		} else {
			$obj = null;
		}
		return $obj;
	}

	public function get($args) {
		$obj = $this->query($args);
		if ($obj) {
			$obj = $this->newObj($obj[0]);
		}
		return $obj;
	}
	
	public function recordExists($id) {
		if(!isset($this->_storage))
			return false;

		$idField = $this->getSchema()->getIdField();
		if (is_array($id) && isset($id[$idField])) {
			$id = $id[$idField];
		}
		return array_key_exists($id, $this->_storage);
	}
	
	/**
	 * Saves the data in the internal storage variable to the YAML file.
	 * @return unknown_type
	 */
	protected function _save() {
		if(!empty($this->_storage) && $this->_storage != $this->_cache) {
			if (extension_loaded('yaml')) {
				$yaml = yaml_emit($this->_storage);
			} else {
				$dumper = new \sfYamlDumper();
				$yaml = $dumper->dump($this->_storage, 2);
			}
			return file_put_contents($this->_dataStoreFile(), $yaml);
		}
	}
	
	private function _dataStoreFile() {
		return $this->_sourceDirectory.'/'.$this->_filename;
	}
	
	public function translateFields($fields) {
		return $fields;
	}
	
	public function install() {
		return false;
	}
	
	public function uninstall() {
		return false;
	}
	
	public function lastRowId() {
		return count($this->_storage);
	}
	
	/**
	 * Loads the data in the external YAML file into the internal storage var.
	 * 
	 * @return boolean True if the information was loaded, false otherwise.
	 */
	protected function _load() {
		if (file_exists($this->_dataStoreFile())) {
			if (extension_loaded('yaml')) {
				if($contents = file_get_contents($this->_dataStoreFile()))
					$this->_storage = yaml_parse($contents);
			} else {
				$yaml = new \sfYamlParser();
				$this->_storage = $yaml->parse(file_get_contents($this->_dataStoreFile()));
			}
			$this->_cache = $this->_storage;
			return true;
		} else {
			return false;
		}
	}
}
