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

require_once 'Cumula/Test/Base.php';

/**
 * BaseComponent Test Class
 * @package Cumula
 * @subpackage Core
 **/

class Test_BaseComponent extends Test_BaseTest {

    /**
     * setUp
     * @param void
     * @return void
     **/

    public function setUp() {
		$this->app = \Cumula\Application::instance();
		if (!$this->app) {
			$this->app = new \Cumula\Application();
		}
		$this->cm = \Cumula\Component\Manager::instance();
		if (!$this->cm) {
			$this->cm = new \Cumula\Component\Manager();
		}
    }

	public function tearDown() {
	}
	 
	public function createInstance() {
		return new TestBaseComponent();
	}

	public function testConstructor() {
		$component = $this->createInstance();
		$this->assertContains(array($component, "startup"),
							  $this->cm->getEventListeners('ComponentStartupComplete'));
		$this->assertContains(array($component, "shutdown"),
							  $this->app->getEventListeners('BootShutdown'));
		// constructConfig was called
		$this->assertEquals($component->config, 5);
	}
}

class TestBaseComponent extends \Cumula\Component\BaseComponent {
	public function startup() {
		
	}
	public function constructConfig() {
		// Skip this for testing
		return 5;
	}
}