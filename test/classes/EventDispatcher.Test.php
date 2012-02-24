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
require_once BASE_DIR.'/classes/EventDispatcher.class.php';
require_once BASE_DIR.'/classes/Application.class.php';

/**
 * EventDispatcher Test Class
 * @package Cumula
 * @subpackage Core
 **/
class Test_EventDispatcher extends Test_BaseTest {
	public $app;
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
    }

	public function tearDown() {
		$this->app->removeEventListeners('EventDispatcherCreated');
	}

	public function createInstance() {
		return new \Cumula\EventDispatcher();
	}

	public function testConstructor() {
		$app = $this->app;
		
		$this->assertInstance(\Cumula\Application::instance(), 'Cumula\\Application');
		
		$that = $this;
		$constructed = false;
		$testFunction = function($event, $dispatcher) use ($that, &$constructed) {
			$that->assertInstance($dispatcher, '\Cumula\EventDispatcher');
			$constructed = true;
		};
		$app->addEventListener('EventDispatcherCreated', $testFunction);
		
		$this->assertTrue(in_array($testFunction, $app->getEventListeners('EventDispatcherCreated')), 'Listener not in listeners array.');
		
		$eventDispatcher = $this->createInstance();
		
		$this->assertTrue(!$constructed, 'EventDispatcherCreated not called.');
		$this->assertTrue($eventDispatcher->eventIsRegistered('EventDispatcherEventDispatched'));
		$this->assertTrue($eventDispatcher->eventIsRegistered('EventListenerRegistered'));
		$this->assertTrue($eventDispatcher->eventIsRegistered('EventLogged'));
	}

	public function testAddRemoveEvent() {
		$event = uniqid("event_");
		$eventDispatcher = $this->createInstance();
		$eventDispatcher->addEvent($event);
		$this->assertContains($event, array_keys($eventDispatcher->getEvents()));
		$this->assertTrue($eventDispatcher->eventIsRegistered($event));

		$eventDispatcher->removeEvent($event);
		$this->assertNotContains($event, array_keys($eventDispatcher->getEvents()));
		$this->assertFalse($eventDispatcher->eventIsRegistered($event));
	}

	public function testAddRemoveEventListener() {
		$event = uniqid("event_");
		$eventDispatcher = $this->createInstance();
		$eventDispatcher->addEvent($event);

		$handler =  function () {};
		$eventDispatcher->addEventListener($event, $handler);
		$this->assertContains($handler, $eventDispatcher->getEventListeners($event));

		$eventDispatcher->removeEventListener($event, $handler);
		$this->assertNotContains($handler, $eventDispatcher->getEventListeners($event));
	}

	public function testAddEventListenerOnlyOnce() {
		$event = uniqid("event_");
		$eventDispatcher = $this->createInstance();
		$eventDispatcher->addEvent($event);

		$handler =  function () {};
		$eventDispatcher->addEventListener($event, $handler);
		$eventDispatcher->addEventListener($event, $handler);
		$this->assertContains($handler, $eventDispatcher->getEventListeners($event));
		$this->assertEquals(count($eventDispatcher->getEventListeners($event)),
							1);
		$eventDispatcher->removeEventListener($event, $handler);
		$this->assertNotContains($handler, $eventDispatcher->getEventListeners($event));
	}

	public function testAddEventListenerByName() {
		$event = uniqid("event_");
		$eventDispatcher = $this->createInstance();
		$eventDispatcher->addEvent($event);

		$eventDispatcher->addEventListener($event, "handle_method");
		$ref = array($eventDispatcher, "handle_method");
		$this->assertContains($ref,
							  $eventDispatcher->getEventListeners($event));
		$eventDispatcher->removeEventListener($event, "handle_method");
		$this->assertNotContains($ref,
								 $eventDispatcher->getEventListeners($event),
								 var_export($eventDispatcher->getEventListeners($event), true));
	}
}