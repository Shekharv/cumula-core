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
require_once 'classes/EventDispatcher.class.php';

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

    /**
     * tearDown
     * @param void
     * @return void
     **/
    public function tearDown() {
        if (is_array($this->files) && count($this->files) > 0) {
            foreach ($this->files as $key => $file) {
                $file = realpath($file);
                if ($file !== FALSE && file_exists($file)) {
                    if (is_dir($file)) {
                        exec('rm -rf '. escapeshellarg($file));
                    }
                    else {
                        unlink($file);
                    }
                    if (file_exists($file)) {
                        printf("Houston, We have a problem.  %s wasn\'t deleted\n", $file);
                    }
                }
            }
        }
    } // end function tearDown
}
