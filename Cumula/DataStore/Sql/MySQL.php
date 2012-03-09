<?php
namespace Cumula\DataStore\Sql;

/**
 * MySQL Data Store
 *
 * Implementation of DataStore that uses a MySQL backend to save data
 *
 * @package Cumula
 * @subpackage CoreData
 * @author Seabourne Consulting
 **/
class MySQL extends PDO
{
	/**
	 * Properties
	 */
	
	/**
	 * Host used to connect to the data store with
	 * @var string
	 **/
	private $host;

	/**
	 * Port to use to connect to the database
	 * @var integer
	 **/
	private $port;
	
	/**
	 * Database to use for the connection
	 * @var string
	 **/
	private $db;

	/**
	 * Public Methods
	 */
	/**
	 * Implementation of translateFields
	 * @param array $fields Array of fields to translate
	 * @return array array of translated fields
	 **/
	public function translateFields($fields) 
	{
		$return = array();
		foreach ($fields as $field => $args) 
		{
			switch($args['type']) 
			{
				case self::FIELD_TYPE_STRING:
					$type = sprintf('VARCHAR(%u)', isset($args['size']) ? $args['size'] : 255);
					break;

				case self::FIELD_TYPE_INTEGER:
					$type = 'INT';
					break;

				case self::FIELD_TYPE_BLOB:
					$type = 'BLOB';
					break;

				case self::FIELD_TYPE_DATETIME:
					$type = 'DATETIME';
					break;

				case self::FIELD_TYPE_FLOAT:
					$type = 'FLOAT';
					break;

				case self::FIELD_TYPE_TEXT:
					$type = 'TEXT';
					break;

				case self::FIELD_TYPE_BOOL:
					$type = 'BOOL';
					break;

				default:
					throw new DataStoreException(sprintf('%s is an invalid data type', $args['type']));
			}
			$new_args = array(
				'type' => $type,
			);

			if (isset($args['default'])) 
			{

				$new_args['default'] = sprintf(' DEFAULT %s', is_numeric($args['default']) ? $args['default'] : "'{$args['default']}'");
			}

			if (isset($args['autoincrement'])) 
			{
				$new_args['autoincrement'] = ' AUTO_INCREMENT NOT NULL UNIQUE';		
			}
			elseif (isset($args['unique']))
			{
				$new_args['autoincrement'] = ' UNIQUE';
			}

			if (isset($args['primary']))
			{
					$new_args['primary'] = ' PRIMARY KEY';
			}


			// If the field is set as autoincrement, it should already be set as NOT NULL
			if (isset($args['null']) && !isset($new_args['autoincrement']))
			{
				$new_args['null'] = ' NOT NULL';
			}

			$return[$field] = $new_args;
		}
		return $return;
	} // end function translateFields
	
	/* (non-PHPdoc)
	 * @see core/interfaces/DataStore#create($obj)
	 */
	public function create($obj) {
		$schemaName = $this->getSchema()->getName();
		$keys = array();
		$values = array();
		foreach ($this->getSchema()->getFields() as $field => $args) 
		{
			if (!isset($obj->$field))
			{
				continue;
			}
			$keys[] = $field;
			$values[] = is_numeric($obj->$field) ? $obj->$field : $this->escapeString($obj->$field);
		}
		$sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $schemaName, implode(', ', $keys), implode(', ', $values));
		return $this->doExec($sql);
	}

	/**
	 * Update an object in the database
	 * @param stdClass $obj Object to be updated
	 * @return MySQLDataStore
	 **/
	public function update($obj) 
	{
		$schema = $this->getSchema();
		$schemaId = $schema->getIdField();

		$updates = array();

		foreach ($schema->getFields() as $name => $field) 
		{
			if ($name !== $schemaId) {
				$updates[] = sprintf('%s = %s', $name, $this->escapeString($obj->$name));
			}
		}
		
		$query = sprintf('UPDATE %s SET %s WHERE %s = %s', $schema->getName(), implode(', ', $updates), $schemaId, $this->escapeString($obj->$schemaId));
		return $this->doExec($query);
	} // end function update


	/**
	 * Create or Update an Object
	 * @param stdClass $obj Object to be saved
	 * @return MySQLDataStore
	 **/
	public function createOrUpdate($obj) 
	{
		$schema = $this->getSchema();
		$schemaId = $schema->getIdField();

		$performUpdate = FALSE;

		if (isset($obj->$schemaId))
		{
			$query = sprintf('SELECT COUNT(*) AS cnt FROM %s WHERE %s = %s', $schema->getName(), $schemaId, $this->escapeString($obj->$schemaId));
			$results = $this->doQuery($query);
			$performUpdate = $results[0]['cnt']  > 0;
		}

		if ($performUpdate) 
		{
			$this->update($obj);
		}
		else 
		{
			$this->create($obj);
		}
	} // end function createOrUpdate

	/**
	 * Get the DSN for the PDO Connection
	 * @param void
	 * @return void
	 **/
	public function getDSN() 
	{
		return sprintf('mysql:host=%s;dbname=%s;port=%s', $this->getHost(), $this->getDb(), $this->getPort());
		
	} // end function getDSN

	/**
	 * Getters and Setters
	 */
	/**
	 * Getter for $this->db
	 * @param void
	 * @return string
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function getDb() 
	{
		return $this->db;
	} // end function getDb()
	
	/**
	 * Setter for $this->db
	 * @param string
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function setDb($arg0) 
	{
		$this->db = $arg0;
		return $this;
	} // end function setDb()
	
	/**
	 * Getter for $this->port
	 * @param void
	 * @return integer
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function getPort() 
	{
		if (is_null($this->port)) 
		{
			$this->setPort(3306);
		}
		return $this->port;
	} // end function getPort()
	
	/**
	 * Setter for $this->port
	 * @param integer
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function setPort($arg0) 
	{
		$this->port = $arg0;
		return $this;
	} // end function setPort()
	
	/**
	 * Getter for $this->host
	 * @param void
	 * @return string
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function getHost() 
	{
		return $this->host;
	} // end function getHost()
	
	/**
	 * Setter for $this->host
	 * @param string
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function setHost($arg0) 
	{
		$this->host = $arg0;
		return $this;
	} // end function setHost()
} // end class MySQLDataStore extends PDODataStore
