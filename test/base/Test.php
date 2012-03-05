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

require_once 'vfsStream/vfsStream.php';

/**
 * BaseTest Class
 *
 * The Base PHPUnit Test Class
 *
 * @package     Cumula
 * @subpackage  Tests
 * @author      Seabourne Consulting
 */
class Test_BaseTest extends PHPUnit_Framework_TestCase {
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
			$this->setupVfs();
    } // end function setUp

		/**
		 * Setup VFS Filestructure
		 * @param void
		 * @return void
		 **/
		private function setupVfs() 
		{
			vfsStream::setup('app');

			$structure = array(
				'app' => array(
					'config' => array(),
					'cache' => array(),
				),
			);

			vfsStream::create($structure);

			defined('APPROOT') ||
				define('APPROOT', vfsStream::url('app'));
			defined('CONFIGROOT') ||
				define('CONFIGROOT', vfsStream::url('app/config'));
		} // end function setupVfs

}
