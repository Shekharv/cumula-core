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
require_once 'libraries/ContentBlock/ContentBlock.class.php';

/**
 * ContentBlock Test class
 * @package Cumula
 * @subpackage Core
 **/
class Test_ContentBlock extends Test_BaseTest {
    /**
     * ContentBlock class
     * @var ContentBlock
     */
    private $contentBlock;

    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
        $this->contentBlock = new \ContentBlock\ContentBlock();
    } // end function setUp

    /**
     * Test the class constructor
     * This test may be a little overkill, but it will be a good base
     * @param void
     * @return void
     * @group all
     * @covers ContentBlock\ContentBlock::__construct
     **/
    public function testConstructor() {
        $contentBlock = new \ContentBlock\ContentBlock();
        // Test the default values
        $this->assertInternalType('array', $contentBlock->data);
        $this->assertEquals(0, count($contentBlock->data));
        $this->assertEquals('', (string)$contentBlock);
    } // end function testConstructor

    /**
     * Test the render method
     * @param void
     * @return void
     * @group all
     * @covers ContentBlock\ContentBlock::render
     **/
    public function testRender() {
        $content = uniqid('content_');

        $this->contentBlock->content = $content;
        $this->assertEquals($content, $this->contentBlock->render());
    } // end function testRender

    /**
     * Test the magic __toString method
     * @param void
     * @return void
     * @group all
     * @covers ContentBlock\ContentBlock::__toString
     **/
    public function testToString() {
        $content = uniqid('content_');
        $this->contentBlock->content = $content;

        $this->assertEquals($content, (string)$this->contentBlock);
    } // end function testToString
} // end class Test_ContentBlock extends Test_BaseTest
