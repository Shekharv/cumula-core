<?php

require_once 'Cumula/Test/Base.php';

/**
 * BaseComponent Test Class
 * @package Cumula
 * @subpackage Core
 **/

class Test_SimpleComponent extends Test_BaseTest {
	
	public function setUp() {
		parent::setUp();
		$this->app = \Cumula\Application\Application::instance();
		if (!$this->app) {
			$this->app = new \Cumula\Application\Application();
		}
		$this->cm = \Cumula\Application\ComponentManager::instance();
		if (!$this->cm) {
			$this->cm = new \Cumula\Application\ComponentManager();
		}
		$this->yf = \Cumula\DataStore\YAML\YAMLFactory::instance();
		if (!$this->yf) {
			$this->yf = new \Cumula\DataStore\YAML\YAMLFactory();
		}
	}
	 
	public function createInstance() {
		return new TestSimpleComponent();
	}
	
	public function testDataStoreStartup() {
		$sc = $this->createInstance();
		$sc->startup();
		$this->assertInstance(
			$sc->dataStores['factory'],
			'Cumula\\DataStore\\YAML\\YAML');
		$this->assertEquals($sc->dataStores['factory']->getSchema()->getFields(),
							$sc->schemas['factory']);
		$this->assertTrue($sc->dataStores['factory']->isConnected());
		$this->assertInstance(
			$sc->dataStores['direct'],
			'Cumula\\DataStore\\YAML\\YAML');
		$this->assertEquals($sc->dataStores['direct']->getSchema()->getFields(),
							$sc->schemas['direct']);
		$this->assertTrue($sc->dataStores['direct']->isConnected());
	}

}

class TestSimpleComponent extends \Cumula\Application\SimpleComponent {
	public $defaultConfig = array(
		'dataStores' => array(
			'factory' => array(
				'factory' => 'Cumula\\DataStore\\YAML\YAMLFactory',
				'source_directory' => DATAROOT,
				'filename' => 'simple_factory.yaml'
				),
			'direct' => array(
				'engine' => 'Cumula\\DataStore\\YAML\YAML',
				'source_directory' => DATAROOT,
				'filename' => 'simple_engine.yaml'
				)
			)
		);
	public $schemas = array(
		'factory' => array(
			'id' => 'string',
			'body' => 'string',
			),
		'direct' => array(
			'id' => 'string',
			'author' => 'string',
			)
		);
}