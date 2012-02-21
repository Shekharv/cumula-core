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
require_once BASE_DIR.'/classes/BaseComponent.class.php';
require_once BASE_DIR.'/classes/Application.class.php';
require_once BASE_DIR.'/classes/ComponentManager.class.php';

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
		$this->cm = \Cumula\ComponentManager::instance();
		if (!$this->cm) {
			$this->cm = new \Cumula\ComponentManager();
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

class TestBaseComponent extends \Cumula\BaseComponent {
	public function startup() {
		
	}
	public function constructConfig() {
		// Skip this for testing
		return 5;
	}
}