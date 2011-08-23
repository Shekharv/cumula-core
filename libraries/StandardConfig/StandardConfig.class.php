<?php
namespace Cumula;
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
 * StandardConfig Class
 *
 * The StandardConfig represents the standard configuration storage and access mechanism for components.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
class StandardConfig implements CumulaConfig {
	private $_dataStore;
	
	/**
	 * Constructor
	 * 
	 * @param $source_directory
	 * @param $source_file
	 * @return unknown_type
	 */
	public function __construct($source_directory, $source_file) {
		global $App;
		$schema = new SimpleSchema('id', array('id' => 'integer',
					 'value' => 'string'));
		$this->_dataStore = new YAMLDataStore($schema, array('source_directory' => $source_directory, 'filename' => $source_file));
		$this->_dataStore->connect();
	}
	
	/**
	 * Ensures that the configuration info is saved
	 * 
	 * @return unknown_type
	 */
	public function __destruct() {
		$this->_dataStore->disconnect();
	}
	
	/* (non-PHPdoc)
	 * @see core/interfaces/CumulaConfig#getConfigValue($config)
	 */
	public function getConfigValue($config, $default = null) {
		//$obj = null;
		$obj = $this->_dataStore->query($config);
		if (isset($obj)) {
			return $obj;
		} else {
			return $default;
		}
	}
	
	/* (non-PHPdoc)
	 * @see core/interfaces/CumulaConfig#setConfigValue($config, $value)
	 */
	public function setConfigValue($config, $value) {
		$obj = array($config => $value);
		$this->_dataStore->createOrUpdate($obj);
	}
	
	/* (non-PHPdoc)
	 * @see core/interfaces/CumulaConfig#deleteConfigValue($config)
	 */
	public function deleteConfigValue($config) {
		$obj = new stdClass();
		$obj->id = $config;
		$this->_dataStore->delete($obj);
	}
	
	/* (non-PHPdoc)
	 * @see core/interfaces/CumulaConfig#toXml()
	 */
	public function toXml() {
		//TODO	
	}
	
	public function toYaml() {
		//TODO
	}
	
	public function toArray() {
		//TODO
	}
	
	public function toString() {
		//TODO
	}
	
	public function serialize() {
		//TODO
	}
	
	public function unserialize() {
		//TODO	
	}
}
