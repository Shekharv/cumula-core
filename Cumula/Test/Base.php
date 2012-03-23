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

/**
 * BaseTest Class
 *
 * The Base PHPUnit Test Class
 *
 * @package     Cumula
 * @subpackage  Tests
 * @author      Seabourne Consulting
 */
abstract class Test_BaseTest extends PHPUnit_Framework_TestCase {
    /**
     * Files to delete on tearDown
     * @var array
     */
    protected $files = array();

	public function assertInstance($instance, $class) {
		self::assertTrue(isset($instance), 'Class not set');
		$instance_class = get_class($instance);
		self::assertEquals($instance_class, $class, 'Instance '.$instance_class. ' not equal to class: '.$class);
	}

	public function assertDispatches($obj, $event,
									$executor,
									$arg_check = null,
									$callback = null,
									$dispatchType = null) {
		$that = $this;
		$called = false;
		$ret = null;
		
		$testFunction = function($e, $dispatcher) use ($that, &$called, $arg_check, $callback, $dispatchType) {
			if ($dispatchType) {
				$that->assertInstance($dispatcher, $dispatchType);
			}
			$event_args = array_slice(func_get_args(), 2);
			if ($arg_check) {
				$that->assertEquals($arg_check, $event_args,
									"arg_check: ".var_export($event_args, true). " not equal to expected \n ".var_export($arg_check, true));
			}
			if ($callback) {
				$ret = call_user_func($callback, $that, $event_args);
			}
			$called = true;
		};
		$obj->bind($event, $testFunction);
		call_user_func($executor, $this);
		$this->assertTrue($called, "event listener not called for ".$event);
		$obj->unbind($event, $testFunction);
		return $ret;
	}

	public function assertBound($handler, $obj, $event) {
		$listeners = $obj->getEventListeners($event);
		$this->assertContains($handler, $listeners, 'Listener not in listeners array'. var_export($listeners, true));

	}
	
	public function assertIsBound($obj, $event, $that) {
		$found = false;
		$listeners = array_values($obj->getEventListeners($event));
		foreach($listeners as $handler) {
			if (is_array($handler)) {
				if ($handler[0] = $that) {
					$found = true;
				}
			}
		}
		$this->assertTrue($found, get_class($that)." is not bound to ".get_class($obj)." for ".$event);
	}
	/**
	 * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    } // end function setUp

}
