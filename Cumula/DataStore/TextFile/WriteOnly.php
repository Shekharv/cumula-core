<?php
namespace Logger;
use Cumula\BaseDataStore as BaseDataStore;
//TODO: Figure out what todo with this, whether to keep it.
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
 * WriteOnlyTextDataStore Class
 *
 * The WriteOnlyTextDataStore allows for writing of arbitrary data to a text file.
 *
 * @package		Cumula
 * @subpackage	Logger
 * @author     Seabourne Consulting
 */
class WriteOnlyTextDataStore extends BaseDataStore {
	private $_logFile;

	public function __construct($schema, $configValues) {
		parent::__construct($schema, $configValues);
		$this->_storage = array();
		$this->_logFile = $configValues['logfile'];
	}

	public function connect() {
		return true;
	}

	public function disconnect() {
		return true;
	}

	public function create($obj) {
		@file_put_contents($this->_logFile, $this->_arrayToString($this->_objToArray($obj)), FILE_APPEND);
		@file_put_contents($this->_logFile, "\n", FILE_APPEND);
	}

	public function update($obj) {
		return false;
	}

	public function createOrUpdate($obj) {
		$this->create($obj);
	}

	public function destroy($obj) {
		return false;
	}
	
	public function install() {
		return false;
	}
	
	public function uninstall() {
		return false;
	}
	
	public function translateFields($fields) {
		return $fields;
	}

	public function query($args, $order = null, $sort = null) {
		return false;
	}

	public function recordExists($id) {
		return false;
	}
	
	public function lastRowId() {
		return false;
	}
}
