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

	public function testRegisterEvents() {
		$sc = new TestEventsComponent();
		$sc->startup();

		$this->assertTrue($sc->eventIsRegistered('MyTestEvent'));
	}
	
	public function testDataStoreStartup() {
		$sc = new TestDataStoreComponent();
		$sc->startup();
		$this->assertContains('factory', array_keys($sc->dataStores), var_export($sc->dataStores, true));
		$this->assertInstance(
			$sc->dataStores['factory'],
			'Cumula\\DataStore\\YAML\\YAML');
		$this->assertEquals($sc->dataStores['factory']->getSchema()->getFields(),
							$sc->schemas['factory']);
		$this->assertInstance(
			$sc->dataStores['direct'],
			'Cumula\\DataStore\\YAML\\YAML');
		$this->assertEquals($sc->dataStores['direct']->getSchema()->getFields(),
							$sc->schemas['direct']);

		$this->assertFalse($sc->dataStores['factory']->isConnected());
		$this->assertFalse($sc->dataStores['direct']->isConnected());
		$sc->connectDataStores();
		$this->assertTrue($sc->dataStores['factory']->isConnected());
		$this->assertTrue($sc->dataStores['direct']->isConnected());
	}

	public function testRegisterRoutes() {
		$sc = new TestRoutesComponent();
		$sc->startup();
		A('Router')->collectRoutes(null);
		$this->assertBound(
			array($sc, 'index'),
			A('Router'),
			'/test/one'
			);
		$this->assertBound(
			array($sc, 'detail'),
			A('Router'),
			'/test/two'
			);
	}

	public function testRegisterRoutesSetup() {
		$sc = new TestRoutesSetupComponent();
		$sc->startup();
		A('Router')->collectRoutes(null);
		$this->assertBound(
			array($sc, 'routeStartup'),
			A('Router'),
			'Before/test/one'
			);
		$this->assertBound(
			array($sc, 'routeStartup'),
			A('Router'),
			'Before/test/two'
			);
		$this->assertBound(
			array($sc, 'routeShutdown'),
			A('Router'),
			'After/test/one'
			);
		$this->assertBound(
			array($sc, 'routeShutdown'),
			A('Router'),
			'After/test/two'
			);
	}

}

class TestEventsComponent extends \Cumula\Application\SimpleComponent {
	public $events = array(
		'MyTestEvent'
		);
}


class TestDataStoreComponent extends \Cumula\Application\SimpleComponent {
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

class TestRoutesComponent extends \Cumula\Application\SimpleComponent {
	public $defaultConfig = array(
		'basePath' => '/test'
		);
	public $routes = array(
		'/one' => 'index',
		'/two' => 'detail'
		);

	public function index() {}

	public function detail() {}
}

class TestRoutesSetupComponent extends TestRoutesComponent {
	public function routeStartup() {}

	public function routeShutdown() {}
}