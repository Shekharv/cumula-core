<?php
namespace Cumula\DataStore\Sql;
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
 * SqliteDataStore Class
 *
 * Implementation of DataStore that uses an SQLite backend to save data.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */

class Sqlite extends Base {
	protected $_db;
	protected $_filename;
	protected $_sourceDir;
	
	public function __construct($config) {
		parent::__construct($config);
		
		if(!isset($config['filename']))
			throw new \Exception('Must supply a \'filename\' config value.');
			
		if(!isset($config['sourceDir']))
			throw new \Exception('Must supply a \'sourceDir\' config value.');
			
		$this->_filename = $config['filename'];
		$this->_sourceDir = $config['sourceDir'];
		
		$this->_db = new \SQLite3($this->_dataStoreFile());
		$this->connect();
	}
	
	protected function _dataStoreFile() {
		return $this->_sourceDir.'/'.$this->_filename;
	}
	
	protected function doExec($sql) {
		$this->_log('SQL Executed', $sql);
		return $this->_db->exec($sql);
	}
	
	protected function doQuery($sql) {
		$this->_log('SQL Queried', $sql);
		return $this->_db->query($sql);
	}
	
	public function setup($fields, $id, $domain, $config) {
		parent::setup($fields, $id, $domain, $config);
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#connect()
	 */
	public function connect() {
		$this->doExec($this->install());
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#disconnect()
	 */
	public function disconnect() {
		$this->_db->close();
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#query($args, $order, $limit)
	 */
	public function findByAnyFilter($filters, $order = null, $limit = null, $start = null, $data = null) 
	{
		$result = parent::findByAnyFilter($filters, $order, $limit, $start, $data);
		$arr = array();
		if (!$result )
		{
			return false;
		}
		
		while ($res = $result->fetchArray(SQLITE3_ASSOC)) 
		{
			$arr[] = $res;
		}
		
		if (count($arr) == 0)
		{
			return false;
		}
		else
		{
			return $arr;
		}
	}

	public function recordExists($id) {
		$idField = $this->_getIdField();
		return $this->findByAnyFilter(array($idField => $id));
	}
	
	public function lastObjectId() {
		return $this->_db->lastInsertRowID();
	}
	
	public function translateFields($fields) {
		$return = array();
		foreach($fields as $field => $args) {
			$new_args = array();
			switch($args['type']) {
				case 'string':
					$new_args['type'] = 'TEXT';
					//$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : 255).")";	
					break;
				case 'integer':
					$new_args['type'] = 'INTEGER';
				//	$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : 11).")";	
					break;
				case 'float':
					$new_args['type'] = 'REAL';
					//$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : 11).")";	
					break;
				case 'boolean':
					$new_args['type'] = 'INTEGER';
					//$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : 1).")";	
				case 'text':
					$new_args['type'] = 'TEXT';
				//	$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : null).")";	
					break;
				case 'datetime':
					$new_args['type'] = 'TEXT';
					//$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : null).")";	
					break;
				case 'blob':
					$new_args['type'] = 'TEXT';
					//$new_args['size'] = "(".(array_key_exists('size', $args) ? $args['size'] : null).")";	
					break;
			}
			if(array_key_exists('default', $args))
				$new_args['default'] = " DEFAULT ".is_numeric($args['default']) ? $args['default'] : "'{$args['default']}'";
			if(array_key_exists('autoincrement', $args))
				$new_args['autoincrement'] = ' PRIMARY KEY ';
			if(array_key_exists('primary', $args))
				$new_args['primary'] = ' PRIMARY KEY ';				
			if(array_key_exists('null', $args))
				$new_args['null'] = " NOT NULL ";	
			$return[$field] = $new_args;
		}
		return $return;
	}
	/**
	 * Escape a string
	 * @param string $dirtyString Dirty string to be escaped
	 * @return string Clean String
	 **/
	public function escapeString($dirtyString) 
	{
		return sprintf("'%s'", $this->_db->escapeString($dirtyString));
	} // end function escapeString
}
