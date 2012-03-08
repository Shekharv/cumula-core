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
		self::assertTrue(get_class($instance) == $class, 'Instance not equal to class: '.get_class($instance));
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
