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
require_once 'vfsStream/vfsStream.php';
require_once 'classes/EventDispatcher.class.php';
require_once 'interfaces/CumulaConfig.interface.php';
require_once 'libraries/StandardConfig/StandardConfig.class.php';
require_once 'classes/BaseComponent.class.php';

/**
 * Tests for the BaseComponent Class
 * @package Cumula
 * @author Seabourne Consulting
 */
class Test_BaseComponent extends Test_BaseTest {
    /**
     * Store the component variable
     * @var BaseComponent
     */
    private $component;

    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
			parent::setUp();
			defined('ROOT') || 
					define('ROOT', dirname(BASE_DIR));

			defined('CONFIGROOT') ||
				define('CONFIGROOT', vfsStream::url('app/config'));

			$this->component = new TestBaseComponent();
    } // end function setUp

    /**
     * Test the constructor
     * @param void
     * @return void
     * @author Seabourne Consulting
     * @group all
     * @covers Cumula\BaseComponent::__construct
     **/
    public function testConstructor() {
        $this->assertObjectHasAttribute('config', $this->component);
        $this->assertObjectNotHasAttribute(uniqid(), $this->component);
    } // end function testConstructor

    /**
     * Test the renderPartial
     * @param void
     * @return void
     * @group all
     * @covers Cumula\BaseComponent::renderPartial
     **/
    public function testRenderPartial() {
        $value = uniqid('value_');

        $templateFile = $this->createTemplate($value);
        
        $output = $this->component->renderPartial($templateFile);

        $this->assertEquals($output, $value);
    } // end function testRenderPartial

    /**
     * Test the renderContent method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\BaseComponent::renderContent
     **/
    public function testRenderContent() {
        $this->markTestSkipped('Unable to test this method. It relies on Application::getResponse which is not able to be mocked or overloaded.');
        $value = uniqid('value_');

        $templateFile = $this->createTemplate($value);
        $this->component->render($templateFile);

        $mockResponse = $this->getMock('Response');
        $this->assertTrue(($mockResponse instanceOf Response) === TRUE);
        $response = Application::getResponse();
    } // end function testRenderContent

    /**
     * Create a template and store the contents
     * @param string $contents
     * @return string
     * @author Craig Gardner <craig@seabourneconsulting.com>
     **/
    public function createTemplate($contents, $fileName = NULL) {
        if (is_null($fileName)) {
            $fileName = CONFIGROOT . DIRECTORY_SEPARATOR . 'view.tpl.php';
        }

        if (file_put_contents($fileName, $contents) === FALSE) {
            $this->fail(sprintf('Failed to write to %s', $fileName));
        }
        return $fileName;
    } // end function createTemplate
    
}

class TestBaseComponent extends Cumula\BaseComponent {
	public function rootDirectory() {
		return CONFIGROOT;
	}

	public static function getInfo() {
		return array();
	}
}
