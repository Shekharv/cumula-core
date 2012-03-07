<?php
require_once 'base/Test.php';
require_once 'components/Cache/Cache.component';

/**
 * Cache Component Tests
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 **/
class Test_Cache extends Test_BaseTest 
{
	/**
	 * setUp
	 * @param void
	 * @return void
	 **/
	public function setUp() 
	{
		parent::setUp();
		\A('Cache')->startup();
	} // end function setUp

	/**
	 * Test the get method
	 * @param void
	 * @return void	
	 * @group all
	 **/
	public function testCacheGet() 
	{
		$key = uniqid('key_');
		$value = uniqid('value_');
		$this->assertFalse(\Cache\Cache::get($key));

		\Cache\Cache::set($key, $value);
		$this->assertEquals($value, \Cache\Cache::get($key));
	} // end function testCacheGet

	/**
	 * Test the Cache class with a custom data store
	 * @param void
	 * @return void
	 * @group all
	 **/
	public function testCustomDataStore() 
	{
		$schema = $this->getMockBuilder('Cumula\\CumulaSchema')->getMock();

		$dataStore = $this->getMockBuilder('Cumula\\BaseDataStore')
			->setConstructorArgs(array($schema))
			->getMock();
		$cache = \Cache\Cache::instance();
		$cache->addDataStore('test', $dataStore);
		$this->assertEquals($cache->dataStoreExists('test'), $dataStore);
		$this->assertEquals($cache->getDataStore('test'), $dataStore);

		$this->assertFalse($cache->addDataStore('cache', $dataStore), 'Make sure we cannot overwrite the default data store');

	} // end function testCustomDataStore
} // end class Test_Cache extends Test_BaseTest
