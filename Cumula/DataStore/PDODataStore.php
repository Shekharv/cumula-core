<?php
namespace Cumula\DataStore;
use Sql\Base as BaseSqlDataStore;
use \PDO as PDO;

require_once realpath(dirname(__FILE__) .'/BaseSqlDataStore.class.php');
/**
 * PDO Data Store
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 **/
abstract class PDODataStore extends BaseSqlDataStore
{
	/**
	 * Driver Constants
	 */
	const DRIVER_MYSQL = 'mysql';
	const DRIVER_PGSQL = 'pgsql';
	const DRIVER_SQLITE = 'sqlite';
	const DRIVER_ODBC = 'odbc';
	const DRIVER_MSSQL = 'mssql';
	const DRIVER_OCI = 'oci';
	const DRIVER_FIREBIRD = 'firebird';
	const DRIVER_INFORMIX = 'informix';

	/**
	 * Properties
	 */
	/**
	 * Driver to use for the Connection
	 * @var string
	 **/
	protected $driver;

	/**
	 * PDO Connection Class
	 * @var PDO
	 **/
	protected $pdo;

	/**
	 * User to connect to the database with
	 * @var string
	 **/
	private $user;

	/**
	 * Password used to connect to the dataabase with 
	 * @var string
	 **/
	private $pass;


	/**
	 * Abstract Methods
	 */
	abstract protected function getDSN();

	/**
	 * Public Methods
	 */
	/**
	 * Class Constructor
	 * @param SimpleSchema $schema Schema to use for this DataStore
	 * @param array $config Array of Configuration Options to be set
	 * @return void
	 **/
	public function __construct($schema, array $config)
	{
		parent::__construct($schema, $config);
		$config['schema'] = $schema;
		$this->configure($config);
		$this->connect();
	} // end function __construct

	/**
	 * Connect method
	 * @param void
	 * @return void
	 * @throws DataStoreException
	 **/
	public function connect() 
	{
		try {
			$this->setPDO(new PDO($this->getDsn(), $this->getUser(), $this->getPass()));
			$this->getPDO()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->doExec($this->install());
			return $this;
		}
		catch (Exception $e) {
			$this->handleException($e);
		}
	} // end function connect
	
	/**
	 * Disconnect from the database
	 * @param void
	 * @return void
	 **/
	public function disconnect() 
	{
		// Do nothing
	} // end function disconnect

	/**
	 * Get the Last Row ID
	 * @param void
	 * @return integer
	 * @throws DataStoreException
	 **/
	public function lastRowId() 
	{
		try {
			return $this->getPDO()->lastInsertId();
		}
		catch (Exception $e) {
			$this->handleException($e);
		}
	} // end function lastRowId

	/**
	 * Configure the DataStore
	 * @param array $config array of configuration options
	 * @return MySQLDataStore
	 **/
	public function configure(array $config) 
	{
		if (count($config) > 0) {
			$methods = get_class_methods($this);
			foreach ($config as $key => $value) 
			{
				$method = sprintf('set%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
				if (in_array($method, $methods))
				{
					$this->$method($value);
				}
			}
		}
		return $this;
	} // end function configure

	/**
	 * Perform A Query
	 * @param string $query Query to be performed
	 * @return mixed results
	 **/
	public function doQuery($query) 
	{
		if (is_null($query))
		{
			return FALSE;
		}

		try {
			$results = $this->getPDO()->query($query, PDO::FETCH_ASSOC);
			$return = array();
			if ($results->rowCount() > 0) 
			{
				foreach($results as $row)
				{
					$return[] = $row;
				}
			}
			return $return;
		}
		catch (Exception $e)
		{
			$this->handlException($e);
		}
	} // end function doQuery

	/**
	 * Execute a Query that does not return a result
	 * @param string $query Query to be executed
	 * @return PDODataStore
	 **/
	public function doExec($query) 
	{
		if (!is_null($query))
		{
			try
			{
				$this->getPDO()->query($query);
			}
			catch (Exception $e)
			{
				$this->handleException($e);
			}
		}
		return $this;
	} // end function doExec

	/**
	 * Determine Whether a record exists or not
	 * @param string $id ID of the record being sought
	 * @return boolean Whether or not the record exists
	 **/
	public function recordExists($id) 
	{
		return $this->query($id);
	} // end function recordExists

	/**
	 * Escape a strin to be used in a database query
	 * @param string $dirtyString The string to be escaped
	 * @return string Escaped String
	 **/
	public function escapeString($dirtyString) 
	{
		return $this->getPDO()->quote($dirtyString);
	} // end function escapeString

	/**
	 * Protected Methods
	 */
	/**
	 * Handle Exceptions
	 * @param Excetption $e Exception being handled
	 * @return void
	 * @throws DataStoreException
	 **/
	protected function handleException(Exception $e) 
	{
			$exceptionClass = NULL;
			if (get_class($e) != 'Exception') {
				$exceptionClass = get_class($e) .' ';
			}
			$message = sprintf('%sException: (%s) %s', $exceptionClass, $e->getMessage(), $e->getCode());
			throw new DataStoreException($message, 1, $e);
	} // end function handleException

	/**
	 * Getters and Setters
	 */
	/**
	 * Getter for $this->pdo
	 * @param void
	 * @return PDO
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	protected function getPDO() 
	{
		if (is_null($this->pdo))
		{
			$this->connect();
		}
		return $this->pdo;
	} // end function getPDO()
	
	/**
	 * Setter for $this->pdo
	 * @param PDO
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	protected function setPDO($arg0) 
	{
		$this->pdo = $arg0;
		return $this;
	} // end function setPDO()

	/**
	 * Getter for $this->pass
	 * @param void
	 * @return string
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function getPass() 
	{
		return $this->pass;
	} // end function getPass()
	
	/**
	 * Setter for $this->pass
	 * @param string
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function setPass($arg0) 
	{
		$this->pass = $arg0;
		return $this;
	} // end function setPass()
	
	/**
	 * Getter for $this->user
	 * @param void
	 * @return string
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function getUser() 
	{
		return $this->user;
	} // end function getUser()
	
	/**
	 * Setter for $this->user
	 * @param string
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	public function setUser($arg0) 
	{
		$this->user = $arg0;
		return $this;
	} // end function setUser()
} // end class PDODataStore extends BaseSqlDataStore
