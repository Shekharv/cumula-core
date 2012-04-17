<?php
namespace Cumula\Test;

/**
 * BaseTest Class
 *
 * The Base PHPUnit Test Class
 *
 * @package     Cumula
 * @subpackage  Tests
 * @author      Seabourne Consulting
 */
abstract class Base extends \PHPUnit_Framework_TestCase {
    /**
     * Files to delete on tearDown
     * @var array
     */
    protected $files = array();

	public function assertEq($tested, $expected) {
		$msg = var_export($tested, true) . "\n did not equal \n" . var_export($expected, true);
		self::assertEquals($tested, $expected, $msg);
	}

	public function assertIn($needle, $haystack) {
		$msg = var_export($needle, true) . "\n not in \n" . var_export($haystack, true);
		self::assertContains($needle, $haystack, $msg);
	}

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
		$listeners = $obj->getEventListeners($event);
		if ($listeners) {
			$listeners = array_values($listeners);
			foreach($listeners as $handler) {
				if (is_array($handler)) {
					if ($handler[0] = $that) {
						$found = true;
					}
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

abstract class AppBase extends Base {
	public function setUp() {
		$this->app = \Cumula\Application\Application::instance();
		if (!$this->app) {
			$this->app = new \Cumula\Application\Application();
		}
		$this->cm = \Cumula\Application\ComponentManager::instance();
		if (!$this->cm) {
			$this->cm = new \Cumula\Application\ComponentManager();
		}
		$this->router = \Cumula\Application\Router::instance();
		if (!$this->router) {
			$this->router = new \Cumula\Application\Router();
		}
	}
	
}