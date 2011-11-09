<?php
namespace TextFileDataStore;
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
 * TextFileDataStore Class
 *
 * Implementation of DataStore that saves data in a flat text file.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */

class TextFileDataStore extends \Cumula\BaseDataStore {
	
	private $_logFile;
	
	public function __construct($config_values) {
		$this->_storage = array();
		$this->_logFile = $config_values['logfile'];
	}
	
	public function connect() {
		$this->_load();
	}
	
	public function disconnect() {
		$this->_save();
	}
	
	public function create($obj) {
	
	}
	
	public function update($obj) {
		
	}
	
	public function createOrUpdate($obj) {
		
	}

	public function delete($obj) {
		
	}
	
	public function query($args, $order = null, $limit = null) {

	}
	
	public function recordExists($id) {
		
	}

    /**
     * Implementation of abstract destroy method
     * @param mixed $obj
     * @return void
     **/
    public function destroy($obj) {
        
    } // end function destroy

    /**
     * Implementation of abstract install method
     * @param void
     * @return void
     **/
    public function install() {
        
    } // end function install

    /**
     * Implementation of the abstract uninstall method
     * @param void
     * @return void
     **/
    public function uninstall() {
        
    } // end function uninstall

    /**
     * Implementation of the abstract translateFields method
     * @param mixed $fields
     * @return void
     **/
    public function translateFields($fields) {
        
    } // end function translateFields

    /**
     * Implementation of the abstract lastRowId method
     * @param void
     * @return void
     **/
    public function lastRowId() {
        
    } // end function lastRowId
}
