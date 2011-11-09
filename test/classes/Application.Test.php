<?php

/**
 * Cumula
 *
 * Cumula - Framework for the cloud.
 *
 * @package     Cumula
 * @version     0.1.0
 * @author      Seabourne Consulting
 * @license     MIT LIcense
 * @copyrigt    2011 Seabourne Consulting
 * @link        http://cumula.org
 */

require_once 'base/Test.php';
require_once 'classes/Application.class.php';

/**
 * Tests for the Application Class
 * @package Cumula
 * @subpackage Core
 * @norunTestsInSeparateProcesses
 **/
class Test_Application extends Test_BaseTest {

    private $calls = 0;

    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
			vfsStream::setup('ApplicationRoot');
			defined('ROOT') ||
				define('ROOT', vfsStream::url('ApplicationRoot'));
    } // end function setUp

    /**
     * Test the Constructor
     * @param void
     * @return void
     * @group all
     * @covers Application::__construct
     * @covers Application::_setupConstants
     * @covers Application::_setupBootstrap
     * @dataProvider constructorDataProvider
     **/
    public function testConstructor($paths = NULL) {
			$this->markTestIncomplete();
        if (!is_null($paths)) {
            // The following paths don't get created in the Application class
            foreach ($paths as $path) {
                if (!file_exists($path)) {
                    mkdir ($path, 0777, TRUE);
                }
            }
        }
        else {
            mkdir(ROOT .'/core/components', 0777, TRUE);
            mkdir(ROOT .'/templates', 0777, TRUE);
        }
        
				var_dump($paths);
				exit;
        global $callbackExecuted;
        $callbackExecuted = FALSE;

        $application = new Cumula\Application(array($this, 'constructorCallback'), $paths);

        $this->assertTrue($callbackExecuted, 'Callback was not executed during Application constructor');

        $checkConstants = array(
            'APPROOT',
            'COMPROOT',
            'CONTRIBCOMPROOT',
            'CONFIGROOT',
            'DATAROOT',
            'TEMPLATEROOT',
        );

        // Make sure the constants are all set
        foreach ($checkConstants as $const) {
            $constVal = constant($const);
            // Make sure the constant is longer than "/" and a file that exists
            $this->assertGreaterThan(2, strlen($constVal), sprintf('Make sure the the length of "%s" is greater than 2', $constVal));
            $this->assertFileExists($constVal);
        }
    } // end function testConstructor

		/**
		 * Constructor Callback
		 * @param void
		 * @return void
		 **/
		public function constructorCallback() 
		{
			global $callbackExecuted;   
			$callbackExecuted = TRUE;
			
		} // end function constructorCallback

    /**
     * Test the boot method
     * @param void
     * @return void
     * @group all
     * @covers Application::boot
     **/
    public function notestBoot() {
        $application = new Application(); 

        foreach ($application->bootProcess as $bootEvent) {
            $application->addEventListener($bootEvent, array($this, 'applicationCallback'));
        }

        $application->boot();
        $this->assertEquals(count($application->bootProcess), $this->calls);
    } // end function testBoot

    /**
     * Testing the magic __callStatic method
     * @param void
     * @return void
     * @group all
     * @covers Application::__callStatic
     **/
    public function notestCallStatic() {
        // Need to instantiate the class before we can use the magic method
        // @TODO Make Application::__callStatic instantiate an object if it's not instantiated already
        $null = new ApplicationTestEvent();

        $this->assertInstanceOf('ApplicationTestEvent', Application::getApplicationTestEvent());

        $this->assertFalse(Application::getClassDoesNotExist());
    } // end function testCallStatic

    public function constructorDataProvider() {
        if (defined('ROOT') === FALSE) {
            vfsStream::setup('ApplicationRoot');
            define('ROOT', vfsStream::url('ApplicationRoot'));
        }
        return array(
            'Custom Paths' => array(array(
                'core_path' => ROOT .'/core',
                'core_component_path' => ROOT .'/core_component_path',
                'contrib_component_path' => ROOT .'/contrib_component_path',
                'config_path' => ROOT .'/config_path',
                'data_path' => ROOT .'/data_path',
                'template_path' => ROOT .'/template_path',
            )),
            'No Paths' => array(NULL),
        );
    }

    /**
     * Application Callback method
     * @param void
     * @return void
     **/
    public function applicationCallback() {
        $this->calls++;
    } // end function applicationCallback
} // end class Test_Application extends Test_BaseTest

class ApplicationTestEvent extends Cumula\EventDispatcher {}
