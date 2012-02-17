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

    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
        
    }

	public function createInstance() {
		return new \Cumula\EventDispatcher();
	}

	public function testConstructor() {
		$app = new \Cumula\Application();
		
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
}
