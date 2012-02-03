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
require_once 'classes/EventDispatcher.class.php';

/**
 * EventDispatcher Test Class
 * @package Cumula
 * @subpackage Core
 **/
class Test_EventDispatcher extends Test_BaseTest {
    /**
     * Store the number of calls to a method
     * @var integer
     */
    private $calls = 0;

    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
        $this->eventDispatcher = new EventDispatcherClass();
    } // end function setUp

    /**
     * Test the class Constructor
     * @param void
     * @return void
     * @group all
     * @covers Cumula\EventDispatcher::__construct
     **/
    public function testConstructor() {
        $dispatcher  = new EventDispatcherClass();

        // Make sure the EVENTDISPATCHER_EVENT_DISPATCHED event was registered
        // in the constructor
        $dispatcher->addEventListener('eventdispatcher_event_dispatched', array($this, 'eventDispatched'));
    } // end function testConstructor

    /**
     * Test the addEvent and removeEvent methods
     * @param void
     * @return void
     * @group all
     * @covers Cumula\EventDispatcher::addEvent
     * @covers Cumula\EventDispatcher::removeEvent
     * @covers Cumula\EventDispatcher::eventHashExists
     **/
    public function testEventCreationAndRemoval() {
        $eventId = uniqid('event_');

        $this->eventDispatcher->addEvent($eventId);
				$class = get_class($this->eventDispatcher);
        $this->assertTrue($class::eventHashExists($eventId) !== FALSE);

        $this->eventDispatcher->removeEvent($eventId);
        $this->assertFalse($class::eventHashExists($eventId));
    } // end function testEventCreationAndRemoval

    /**
     * Test the static getInstance method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\EventDispatcher::instance
     * @covers Cumula\EventDispatcher::setInstance
     **/
    public function testStaticGetInstance() {
        $eventId = uniqid('event_');
        $this->eventDispatcher->addEvent($eventId);
        $this->eventDispatcher->addEventListener($eventId, array($this, 'eventDispatched'));

        $instance = EventDispatcherClass::instance();
        $this->assertEquals($this->eventDispatcher, $instance);
    } // end function testStaticGetInstance

		/**
		 * Test the instance() method
		 * @param void
		 * @return void
		 * @group all
		 * @TODO Mock or overload the Router class and make sure the instance() method will return an 
		 * 	instance of the proper class
		 **/
		public function testInstanceMethod() 
		{
			$instance = $this->eventDispatcher->myInstance('Router');
			$this->assertInstanceOf('Cumula\\Router', $instance);

			// Add the class to the autoloader so it can be found
			Cumula\Autoloader::instance()->registerClass('EventDispatcherClass', __FILE__);

			$instance = $this->eventDispatcher->myInstance('EventDispatcherClass');
			$this->assertInstanceOf('EventDispatcherClass', $instance);
			$this->assertEquals($instance, $this->eventDispatcher);
		} // end function testInstanceMethod

    /**
     * test the addEventListener and dispatch methods
     * @param void
     * @return void
     * @group all
     * @covers Cumula\EventDispatcher::addEventListener
     * @covers Cumula\EventDispatcher::dispatch
     **/
    public function testEventListenerAndDispatch() {
        $eventId = uniqid('event_');

        $this->eventDispatcher->addEvent($eventId);
        $this->eventDispatcher->addEventListener($eventId, array($this, 'eventDispatched'));    

        $this->assertTrue($this->eventDispatcher->dispatch($eventId));
        $this->assertFalse($this->eventDispatcher->dispatch(uniqid('event2_')));

        // Make sure the callback was called
        $this->assertEquals(1, $this->calls);
    } // end function testEventListenerAndDispatch

    /**
     * Test the removeEventListener method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\EventDispatcher::removeEventListener
     **/
    public function testRemoveEventListener() {
        $eventId = uniqid('event_');
        $callback = array($this, 'eventDispatched');

        $this->eventDispatcher->addEvent($eventId);
        $this->eventDispatcher->addEventListener($eventId, $callback);

        // Dispatch the event and make sure it fired correctly
        $this->eventDispatcher->dispatch($eventId);
        $this->assertEquals(1, $this->calls);

        // Remove the event and dispatch the event again
        $this->eventDispatcher->removeEventListener($eventId, $callback);
        $this->eventDispatcher->dispatch($eventId);
        $this->assertEquals(1, $this->calls);
    } // end function testRemoveEventListener

    /**
     * Test the addEventListenerTo method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\EventDispatcher::addEventListenerTo
     **/
    public function testAddEventListenerTo() {
        $eventId = uniqid('event_');

        $this->eventDispatcher->addEvent($eventId);

        $dispatcher = new EventDispatcherClass2();
        $dispatcher->addEventListenerTo('EventDispatcherClass', $eventId, array($this, 'eventDispatched'));

        $this->eventDispatcher->dispatch($eventId);
        $this->assertEquals(1, $this->calls);
    }

		/**
		 * Test the getEvents method
		 * @param void
		 * @return void
		 * @group all
		 * @covers Cumula\EventDispatcher::getEvents
		 **/
		public function testGetEvents() 
		{
			$eventsBefore = count($this->eventDispatcher->getEvents());

			$this->eventDispatcher->addEventListenerTo(get_class($this->eventDispatcher), 'testGetEvents', function() {});
			
			$eventsAfter = count($this->eventDispatcher->getEvents());
			$this->assertGreaterThan($eventsBefore, $eventsAfter);
		} // end function testGetEvents

    /**
     * HELPER METHODS |helpers
     */
    /**
     * Callback to test the EVENT_DISPATCHER_EVENT_DISPATCHED event
     * @param void
     * @return void
     * @author Craig Gardner <craig@seabourneconsulting.com>
     **/
    public function eventDispatched() {
			$this->calls++;
    } // end function eventDispatched
} // end class Test_EventDispatcher extends Test_BaseTest

class EventDispatcherClass extends Cumula\EventDispatcher {
	/**
	 * proxy method for Cumula\EventDispatcher::instance()
	 **/
	public function myInstance($className) 
	{
		return \I($className);
	} // end function myInstance
}

class EventDispatcherClass2 extends Cumula\EventDispatcher {
    public $calls = 0;
    
    public function eventCallback() {
        $this->calls++;
    }
}

class EventDispatcherClass3 extends Cumula\EventDispatcher {}
