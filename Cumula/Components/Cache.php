<?php
namespace Cache;

/**
 * Cache Component
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 * @TODO Create get/set datastore methods
 * @TODO Allow developers to override how the caches are stored based on the bin (ie. Cache::get('cacheKey', 'myBin') 
 * 		would allow the developer to hook into cache_get_myBin to return the cached value and store the cache differently
 **/
class Cache extends \Cumula\BaseComponent 
{
	/**
	 * Properties
	 */
	const PERMANENT = 0;

	/**
	 * DataStore Object
	 * @var BaseDataStore
	 **/
	private $dataStores = array();

	/**
	 * Cache Setup
	 * @param void
 	 * @return void
	 **/
	public function startup() 
	{
		$this->addEventListenerTo('Autoloader', 'EventAutoload', array($this, 'populateAutoloader'));
		$this->configureDataStore();
	} // end function startup

	/**
	 * Populate the Autoloader
	 * @param string $event Name of the event being dispatched
	 * @param EventDispatcher $dispatcher Object that dispatched the event
	 * @return void
	 **/
	public function populateAutoloader($event, $dispatcher, $className) 
	{
		return array(
			'Cache\\Schema' => dirname(__FILE__) .'/lib/Schema.class.php',
		);
	} // end function populateAutoloader

	/**
	 * Configure the data store
	 * @param void
	 * @return void
	 **/
	private function configureDataStore() 
	{
		// By default, use the default data store for saving values in the cache
		$dataStoreClass = $this->defaultDataStore();
		$schema = new Schema();
		$this->addDataStore('cache', new $dataStoreClass($schema, array(
			'source_directory' => APPROOT . DIRECTORY_SEPARATOR . 'cache/',
			'filename' => '.cache.db',
		)));
	} // end function configureDataStore

	/**
	 * Get an item from the cache
	 * @param string $cacheName name of the item to fetch from the cache
	 * @param string $bin Name of the bin to fetch the cache from
	 * @return mixed
	 **/
	public static function get($cacheName, $bin = 'cache') 
	{
		$instance = self::instance();
		$instance->dispatch('cache_populate_datastores');
		$cache = (array)$instance->getDataStore($bin)->query(array('cid' => $cacheName), array(), 1);
		return isset($cache[0]['data']) ? unserialize($cache[0]['data']) : FALSE;
	} // end function get

	/**
	 * Add an item to the cache
	 * @param string $cacheName name to store in the cache
	 * @param mixed $value The value to store in the cache
	 * @param array $options Optional array of options
	 * @return void
	 **/
	public static function set($cacheName, $value, array $options = array())
	{
		$options += array(
			'bin' => 'cache',
			'expire' => '15 minutes',
		);
		$instance = self::instance();
		$instance->dispatch('cache_populate_datastores');

		if ($options['expire'] !== Cache::PERMANENT)
		{
			if (is_string($options['expire']))
			{
				$interval = \DateInterval::createFromDateString($options['expire']);
				if (($interval instanceOf \DateInterval) == FALSE)
				{
					return;
				}
				$date = new \DateTime();
				$expires = $date->add($interval)->getTimestamp();
			}
			elseif (is_int($options['expire']))
			{
				$expireObj = new \DateTime($options['expire']);
				$expires = $expireObj->getTimestamp();
				if ($expires != $options['expire']) {
					return;
				}
			}
		}
		else
		{
			$expires = 0;
		}

		$dataStore = $instance->getDataStore($options['bin']);

		$obj = $dataStore->newObj();
		$obj->cid = $cacheName;
		$obj->expire = $expires;
		$obj->created = microtime(TRUE);
		$obj->data = serialize(str_replace(array("\n", "\r"), '', $value));

		$dataStore->createOrUpdate($obj);
	} // end function set

	/**
	 * Get the data store for a given bin
	 * @param string $bin Bin to get the data store for
	 * @return DataStore or FALSE
	 **/
	public function getDataStore($bin) 
	{
		if (($store = $this->dataStoreExists($bin)) === FALSE) {
			$store = $this->dataStoreExists('cache');
		}
		return $store;
	} // end function getDataStore

	/**
	 * Determine whether a data store exists for the given bin
	 * @param string $bin cache bin to verify
	 * @return mixed Returns FALSE if the store doesn't exist and the store if it does
	 **/
	public function dataStoreExists($bin) 
	{
		$stores = $this->getDataStores();
		return isset($stores[$bin]) ? $stores[$bin] : FALSE;
	} // end function dataStoreExists

	/**
	 * Get Info method
	 * @param void
	 * @return array
	 **/
	public static function getInfo() 
	{
		return array(
			'name' => 'Cache Handler',
			'description' => 'Handle the getting and setting of caches',
			'version' => '0.1',
			'dependencies' => array(),
		);
	} // end function getInfo
 
	/**
	 * Add a DataStore
	 * @param string $bin name of the bin to use for the data store
	 * @param BaseDataStore $store Data Store Instance to store
	 * @return Cache\Class
	 **/
	public function addDataStore($bin, \Cumula\BaseDataStore $store) 
	{
		$stores = $this->getDataStores();
		if (get_called_class() != __CLASS__ && $bin == 'cache' || isset($stores[$bin])) {
			return FALSE;
		}
		$stores[$bin] = $store;
		return $this->setDataStores($stores);
	} // end function addDataStore

	/**
	 * Getters and Setters
	 */
	/**
	 * Getter for $this->dataStore
	 * @param void
	 * @return BaseDataStore
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	private function getDataStores() 
	{
		return $this->dataStores;
	} // end function getDataStores()
	
	/**
	 * Setter for $this->dataStore
	 * @param BaseDataStores
	 * @return void
	 * @author Craig Gardner <craig@seabourneconsulting.com>
	 **/
	private function setDataStores($arg0) 
	{
		$this->dataStores = $arg0;
		return $this;
	} // end function setDataStores()
} // end class Cache extends \Cumula\BaseComponent
