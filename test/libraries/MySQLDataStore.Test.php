<?php

require_once realpath(dirname(__FILE__) .'/../base/Test.php');
require_once realpath(dirname(__FILE__) .'/../../classes/EventDispatcher.class.php');
require_once realpath(dirname(__FILE__) .'/../../classes/BaseDataStore.class.php');
require_once realpath(dirname(__FILE__) .'/../../libraries/MySQLDataStore/MySQLDataStore.class.php');

/**
 * Test class for the MySQLDataStore class
 * @package Cumula
 * @subpackage Libraries
 **/
class Test_MySQLDataStore extends Test_BaseTest 
{
	/**
	 * MySQLDataStoreInstance
	 * @var MySQLDataStore
	 **/
	private $instance;

	/**
	 * Options used to create $this->instance
	 * @var array
	 **/
	private $options;

	/**
	 * Schema to be used with the test
	 * @var SimpleSchema
	 **/
	private $schema;

	/**
	 * setUp
	 * @param void
	 * @return void
	 **/
	public function setUp() 
	{
		$this->markTestIncomplete();
		$this->options = array(
			'host' => 'localhost',
			'user' => 'root',
			'pass' => 'root',
			'db' => 'testdb',	
		);

		$this->schema = $this->getMock('Cumula\\SimpleSchema');
	} // end function setUp

	/**
	 * Test the config() method
	 * @param void
	 * @return void
	 * @group all
	 **/
	public function testConfig() 
	{
		$this->markTestIncomplete();
		$this->instance = new Cumula\MySQLDataStore($this->schema, $this->options);
		foreach ($this->options as $key => $value) {
			$method = sprintf('get%s', str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
			$this->assertTrue(is_callable(array($this->instance, $method)), "Testing $method");
			$this->assertEquals($value, $this->instance->$method(), "Checking $key");
		}
		
	} // end function testConfig
	
} // end class Test_MySQLDataStore extends Test_BaseTest
