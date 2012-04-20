<?php

use \Cumula\Application\Response as Response;

/**
 * Response Class Tests
 * The send404 and send405 methods cannot be tested
 * @package Cumula
 * @subpackage Core
 **/
class Test_Response extends \Cumula\Test\Base {
    /**
     * Store the Response Object
     * @var Response
     */
    private $response;
    
    /**
     * setUp
     * @param void
     * @return void
     **/
    public function setUp() {
			parent::setUp();
			$this->response = new Response();
    } // end function setUp

    /**
     * Test the Response Constructor method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\Response::__construct
     **/
    public function testConstructor() {
        $this->assertEquals('', $this->response->content);
    } 

} 
