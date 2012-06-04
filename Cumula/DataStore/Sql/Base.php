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
 * BaseSQLDataStore Class
 *
 * Abstract Class for all SQL derived Data Stores.  Takes care of some of the common code, like creating tables and CRUD operations.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */

abstract class Base extends \Cumula\Base\DataStore {
	private $_name;
	
	protected $_db;
	/**
	 * Schema Object Used for this DataStore
	 * @var SimpleSchema
	 **/
	private $schema;

	public function __construct($config) {
		parent::__construct($config);
		if(!isset($config['tableName']))
			throw new \Exception("Must provide a 'tableName' config value.");
			
		$this->_name = $config['tableName'];
	}

	abstract public function escapeString($dirtyString);

	
	protected function doExec($sql) {
		
	}
	
	protected function doQuery($sql) {
		
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#create($obj)
	 */
	public function create($obj) {
		$sql = "INSERT INTO {$this->_name} ";
		$keys = array();
		$values = array();
		foreach($this->_fields as $field => $args) {
			if(!isset($obj->$field))
				continue;
			$keys[] = "`$field`";
			$values[] = is_numeric($obj->$field) ? $obj->$field : $this->escapeString($obj->$field);
		}
		$sql .= "(".implode(',', $keys).")";
		$sql .= "VALUES (".implode(',', $values).");";
		return $this->doExec($sql);
	}
	
	public function install() {
		$sql_output = "CREATE TABLE IF NOT EXISTS {$this->_name}(";
		$fields = array();
		foreach(static::translateFields($this->_fields) as $field => $attrs) {
			$field = "`$field` {$attrs['type']}";
			if(array_key_exists('size', $attrs))
				$field .= $attrs['size'];
			if(array_key_exists('default', $attrs))
				$field .= $attrs['default'];
			if(array_key_exists('autoincrement', $attrs))
				$field .= $attrs['autoincrement'];
			if(array_key_exists('primary', $attrs))
				$field .= $attrs['primary'];	
			$fields[] = $field;	
		}
		$sql_output .= implode(', ', $fields).');';
		return $sql_output;
	}
	
	public function uninstall() {
		return "DROP TABLE {$this->_name}";
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#update($obj)
	 */
	public function update($obj) {
		$this->_log('BaseSQLDataStore::update called');
		$idField = $this->_getIdField();
		if (!$this->recordExists($obj->$idField)) 
		{
			return false;
		}
		$sql = "UPDATE {$this->_name} SET ";
		$fields = array();
		foreach ($this->_config['fields'] as $field => $args) 
		{
			if (property_exists($obj, $field)) 
			{
				$fields[] = " `$field`=" . (is_numeric($obj->$field) ? $obj->$field : $this->escapeString($obj->$field));
			}
		}
		$sql .= implode(", ", $fields);
		$id = is_numeric($obj->$idField) ? $obj->$idField : $this->escapeString($obj->$idField);
		$sql .= " WHERE {$idField}=". $id .";";
		return $this->doExec($sql);
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#delete($obj)
	 */
	public function destroy($obj) {
		$idField = $this->_getIdField();
		$sql = "DELETE FROM {$this->_name} WHERE ";
		if(is_numeric($obj))
			$sql .= $idField.' = '.$obj.';';
		else
			$sql .= $idField.' = "'.$obj->$idField.'";';
		return $this->doExec($sql);
	}

	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#query($args, $order, $limit)
	 */
	public function findByAnyFilter($filters, $order = null, $limit = null, $start = null, $data = null) {
		$sql = "SELECT * FROM {$this->_name} ";
		//Args is an id
		if (is_array($filters) && !empty($filters)) {
			$conditions = array();
			$sql .= 'WHERE ';
			foreach($filters as $key => $val) {
				$conditions[] = " ".$key."=" . (is_numeric($val) ? $val : $this->escapeString($val));
			}
			$sql .= implode(' OR ', $conditions);
		// Args is a string of sql that will be appended
		} else {
			// do nothing, no args passed
		}
    
    if (!empty($order) && is_array($order)) {
      $order_clause = array();
      foreach ($order as $fieldname => $direction) {
        $order_clause[] = $fieldname.' '.$direction;
      }
      $sql .= ' ORDER BY '.implode(',', $order_clause);
    }
    
    if ($limit && !empty($limit)) {
      if (is_int($limit)) 
        $sql .= ' LIMIT '.$limit;
      elseif (is_array($limit) && count($limit) == 1)
        $sql .= ' LIMIT '.$limit[0];
      elseif (is_array($limit) && count($limit) == 2)
        $sql .= ' LIMIT '.implode (',', $limit);
      else {
        // something invalid.  do nothing.
      }
    }
    
		$sql .= ';';
		return $this->doQuery($sql);
	}

	public function get($args) {
		$obj = $this->findByAnyFilter(array($this->_config['idField'] => $args));
		if ($obj) {
			$obj = $this->newObj($obj[0]);
		}
		return $obj;
	}
  
  /**
   * Execute raw SQL.  CAUTION: this function does zero escaping or other work.
   * You MUST make sure your query is sanitized before you use this function.
   * @param str $sql
   * @return result 
   */
  public function queryRaw($sql)
  {
    return $this->doQuery($sql);
  }

  
	public function recordExists($id) {
	}
	/**
	 * Getter for $this->schema
	 * @param void
	 * @return SimpleSchema
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function getSchema() 
	{
		return $this->schema;
	} // end function getSchema()
	
	/**
	 * Setter for $this->schema
	 * @param SimpleSchema
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function setSchema(\Cumula\Schema\Simple $arg0) 
	{
		if (($arg0 instanceOf \Cumula\Base\Schema) === FALSE)
		{
			throw new \Exception('Schema is not an instance of BaseSchema');
		}
		$this->schema = $arg0;
		return $this;
	} // end function setSchema()

}
