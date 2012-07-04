<?php

/**
 * BaseComponent Test Class
 * @package Cumula
 * @subpackage Core
 **/

class Test_SimpleComponent extends \Cumula\Test\Base {
	
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
	}

	public function testRegisterEvents() {
		$sc = new TestEventsComponent();
		$sc->startup();

		$this->assertTrue($sc->eventIsRegistered('MyTestEvent'));
	}
	
	public function testDataStoreStartup() {
		$sc = new TestDataStoreComponent();
		$sc->startup();
		$this->assertIn('direct', array_keys($sc->dataProviders));
		$this->assertInstance(
			$sc->dataProviders['direct'],
			'Cumula\\DataStore\\YAML\\YAML');
		$this->assertFalse($sc->dataProviders['direct']->isConnected());
		$sc->connectDataProviders();
		$this->assertTrue($sc->dataProviders['direct']->isConnected());
		$this->assertEq(
			$sc->dataProviders['direct'],
			$sc->direct
			);
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
		$this->assertBound(
			array($sc, 'three'),
			A('Router'),
			'/test/three'
			);
		$this->assertBound(
			array($sc, 'under_score'),
			A('Router'),
			'/test/under-score'
			);
	}

	public function testRegisterRootRoutes() {
		$sc = new TestRootRoutesComponent();
		$sc->startup();
		A('Router')->collectRoutes(null);
		$this->assertBound(
			array($sc, 'index'),
			A('Router'),
			'/'
			);
		$this->assertBound(
			array($sc, 'detail'),
			A('Router'),
			'/$id'
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
		'dataProviders' => array(
			'direct' => array(
				'engine' => 'Cumula\\DataStore\\YAML\YAML',
				'sourceDir' => DATAROOT,
				'filename' => 'simple_engine.yaml',
				'fields' => array('id', 'value'),
				'idField' => 'id'
				)
			)
		);
}

class TestRoutesComponent extends \Cumula\Application\SimpleComponent {
	public $defaultConfig = array(
		'basePath' => 'test'
		);
	public $routes = array(
		'one' => 'index',
		'/two' => 'detail',
		'three',
		'under-score',
		);

	public function index() {}

	public function detail() {}
}

class TestRootRoutesComponent extends TestRoutesComponent {
	public $defaultConfig = array(
		'basePath' => ''
		);
	public $routes = array(
		'' => 'index',
		'$id' => 'detail'
		);
}

class TestRoutesSetupComponent extends TestRoutesComponent {
	public function routeStartup() {}

	public function routeShutdown() {}
}