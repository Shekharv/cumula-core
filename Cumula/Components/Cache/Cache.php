<?php
namespace Cumula\Components\Cache;

const SQLite = '\\Cumula\\DataStore\\Sql\\Sqlite';

/**
 * Cache Component
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 * @TODO Create get/set datastore methods
 * @TODO Allow developers to override how the caches are stored based on the bin (ie. Cache::get('cacheKey', 'myBin') 
 * 		would allow the developer to hook into cache_get_myBin to return the cached value and store the cache differently
 **/
class Cache extends \Cumula\Application\SimpleComponent 
{
	/**
	 * Properties
	 */
	const PERMANENT = 0;

	public $defaultConfig = array(
		'dataProviders' => array(
			'cache' => array(
				'engine' => SQLite,
				'fields' => array(
					'cid' => array(
						'type' => 'string',
						'required' => TRUE,
						'unique' => TRUE,
						),
					'data' => array(
						'type' => 'text'
						),
					'expire' => array(
						'type' => 'integer',
						),
					'created' => array(
						'type' => 'integer',
						),
					),
				'idField' => 'cid',
				'sourceDir' => DATAROOT,
				'filename' => 'cache.sqlite',
				'tableName' => 'cache',
				)
			)
		);

	/**
	 * Get an item from the cache
	 * @param string $cacheName name of the item to fetch from the cache
	 * @param string $bin Name of the bin to fetch the cache from
	 * @return mixed
	 **/
	public function get($cacheName, $bin = 'cache') 
	{
		$this->dispatch('cache_populate_datastores');
		$cache = $this->dataProviders[$bin]->get($cacheName);
		$return = false;
		if($cache && isset($cache->data) && $cache->expire > time())
			$return = unserialize($cache->data);
		if($cache && $cache->expire < time())
			$this->dataProviders[$bin]->destroy($cache);
		return $return;
	} // end function get

	/**
	 * Add an item to the cache
	 * @param string $cacheName name to store in the cache
	 * @param mixed $value The value to store in the cache
	 * @param array $options Optional array of options
	 * @return void
	 **/
	public function set($cacheName, $value, array $options = array())
	{
		$options += array(
			'bin' => 'cache',
			'expire' => '15 minutes',
		);
		
		$this->dispatch('cache_populate_datastores');

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

		$dataStore = $this->dataProviders[$options['bin']];

		$obj = $dataStore->newObj();
		$obj->cid = $cacheName;
		$obj->expire = $expires;
		$obj->created = microtime(TRUE);
		$obj->data = serialize(str_replace(array("\n", "\r"), '', $value));

		$dataStore->createOrUpdate($obj);
	} // end function set

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
	public function addDataStore($bin, \Cumula\Base\DataStore $store) 
	{
		$stores = $this->dataProviders;
		if (get_called_class() != __CLASS__ && $bin == 'cache' || isset($stores[$bin])) {
			return FALSE;
		}
		$this->dataProviders[$bin] = $store;
	} // end function addDataStore

} 
