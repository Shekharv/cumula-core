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
		$this->app = \Cumula\Application\Application::instance();
		if (!$this->app) {
			$this->app = new \Cumula\Application\Application();
		}
    }

	public function tearDown() {
		if ($this->app) {
			$this->app->unbindAll('EventDispatcherCreated');
		}
	}

	public function createInstance() {
		return new \Cumula\Application\EventDispatcher();
	}

	public function testConstructor() {
		$app = $this->app;
		
		$this->assertInstance(\Cumula\Application\Application::instance(), 'Cumula\\Application\\Application');
		
		$that = $this;
		$constructed = false;
		$testFunction = function($event, $dispatcher) use ($that, &$constructed) {
			$that->assertInstance($dispatcher, '\Cumula\Application\EventDispatcher');
			$constructed = true;
		};
		$app->bind('EventDispatcherCreated', $testFunction);

		$this->assertBound($testFunction, $app, 'EventDispatcherCreated');
		
		$eventDispatcher = $this->createInstance();
		
		$this->assertTrue(!$constructed, 'EventDispatcherCreated not called.');
		$this->assertTrue($eventDispatcher->eventIsRegistered('EventDispatcherEventDispatched'), "eventdispatched not registered");
		$this->assertTrue($eventDispatcher->eventIsRegistered('EventListenerRegistered'), "eventregistered not registered");
		$this->assertTrue($eventDispatcher->eventIsRegistered('EventLogged'), "logged not registered");
	}

	public function testAddRemoveEvent() {
		$event = uniqid("event_");
		$eventDispatcher = $this->createInstance();
		$eventDispatcher->addEvent($event);
		$this->assertContains($event, array_keys($eventDispatcher->getEvents()),
							  var_export(array_keys($eventDispatcher->getEvents()), true));
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
		$eventDispatcher->bind($event, $handler);
		$this->assertContains($handler, $eventDispatcher->getEventListeners($event),
							  var_export($eventDispatcher->getEvents(), true));

		$eventDispatcher->unbind($event, $handler);
		$this->assertNotContains($handler, $eventDispatcher->getEventListeners($event));
	}

	public function testAddEventListenerOnlyOnce() {
		$event = uniqid("event_");
		$eventDispatcher = $this->createInstance();
		$eventDispatcher->addEvent($event);

		$handler =  function () {};
		$eventDispatcher->bind($event, $handler);
		$eventDispatcher->bind($event, $handler);
		$this->assertContains($handler, $eventDispatcher->getEventListeners($event));
		$this->assertEquals(count($eventDispatcher->getEventListeners($event)),
							1);
		$eventDispatcher->unbind($event, $handler);
		$this->assertNotContains($handler, $eventDispatcher->getEventListeners($event));
	}

}