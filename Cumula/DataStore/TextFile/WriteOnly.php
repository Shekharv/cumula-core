<?php
namespace Cumula\DataStore\TextFile;
use Cumula\Base\FileDataStore as BaseDataStore;
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
class WriteOnly extends BaseDataStore {
	private $_storage = array();

	public function connect() {
		return true;
	}

	public function disconnect() {
		return true;
	}

	public function create($obj) {
		@file_put_contents($this->_dataStoreFile(), $this->_arrayToString($this->_objToArray($obj))."\n", FILE_APPEND);
	}

	public function recordExists($obj) {
		return false;
	}
}
