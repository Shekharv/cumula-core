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
		$this->app = \Cumula\Application\Application::instance();
		if (!$this->app) {
			$this->app = new \Cumula\Application\Application();
		}
		$this->cm = \Cumula\Application\ComponentManager::instance();
		if (!$this->cm) {
			$this->cm = new \Cumula\Application\ComponentManager();
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

	public function testGetConfigValue() {
		$component = new TestConfigComponent();

		$component->setConfigValue('test', 'space');
		$this->assertEquals('space',
							$component->getConfigValue('test'));
		$this->assertEquals('passed_default',
							$component->getConfigValue('notset', 'passed_default'));
		$this->assertEquals('class_default',
							$component->getConfigValue('defaultset'));
		$this->assertEquals('class_default',
							$component->getConfigValue('defaultset', 'passed_default'));
	}
}

class TestBaseComponent extends \Cumula\Base\Component {
	public function startup() {
		
	}
	public function constructConfig() {
		// Skip this for testing
		return 5;
	}
}

class TestConfigComponent extends \Cumula\Base\Component {
	public $defaultConfig = array(
		'defaultset' => 'class_default',
		);
	public function startup() {
		
	}
}