<?php
require_once 'base/Test.php';
require_once 'classes/Autoloader.class.php';

use \Cumula\Autoloader as Autoloader;

/**
 * Unit Tests for the Cumula Autoloader
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 **/
class Test_Autoloader extends Test_BaseTest 
{
	/**
	 * setUp
	 * @param void
	 * @return void
	 **/
	public function setUp() 
	{
		parent::setUp();

		Autoloader::setup();
	} // end function setUP

	/**
	 * tearDown
	 * @param void
	 * @return void
	 **/
	public function tearDown() 
	{
		$autoloader = new Autoloader;
		Autoloader::setInstance($autoloader);
	} // end function tearDown

	/**
	 * Make sure the Autoloader was registered
	 * @param void
	 * @return void
	 * @group all
	 * @covers Cumula\Autoloader::setup
	 **/
	public function testAutoloaderRegistered() 
	{
		Autoloader::setup();
		$functions = spl_autoload_functions();
		$this->assertTrue(in_array(array('Cumula\\Autoloader', 'load'), $functions));
	} // end function testAutoloaderRegistered

	/**
	 * Test the absoluteClassName method
	 * @param void
	 * @return void
	 * @group all
	 * @covers Cumula\Autoloader::absoluteClassName
	 **/
	public function testAbsoluteClassName() 
	{
		// Populate the Autoloader
		Autoloader::load('SomeMiserableClass');

		$this->assertEquals('Cumula\\Router', Autoloader::absoluteClassName('Router'));

		Autoloader::instance()->registerClass('MyAutoloaderClass\\Router', __FILE__);
		$this->assertEquals('MyAutoloaderClass\\Router', Autoloader::absoluteClassName('Router'));
	} // end function testAbsoluteClassName
} // end class Test_Autoloader extends Test_BaseTest
