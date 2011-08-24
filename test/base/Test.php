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

require_once('vfsStream/vfsStream.php');

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
      
    } // end function setUp

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
