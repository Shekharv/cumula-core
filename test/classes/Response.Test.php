<?php

use Cumula\Response as Response;

require_once 'base/Test.php';
require_once 'classes/EventDispatcher.class.php';
require_once 'classes/Request.class.php';
require_once 'classes/Response.class.php';

/**
 * Response Class Tests
 * The send404 and send405 methods cannot be tested
 * @package Cumula
 * @subpackage Core
 **/
class Test_Response extends Test_BaseTest {
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
        $response = array(
            'headers' => array(),
            'content' => '',
            'status_code' => 200,
            'data' => array(),
        );
        $this->assertEquals($response, $this->response->response);
    } // end function testConstructor

    /**
     * Test the sendRawResponse method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\Response::sendRawResponse
     * @covers Cumula\Response::_sendHeader
     **/
    public function testSendRawResponse() {
        $body = uniqid('body_');
        $headers = array(
            'MyHeader' => uniqid('myheader_'),
            'Content-type' => 'text/plain',
        );

        ob_start();
        $this->response->sendRawResponse($headers, $body, 200);
        $contents = ob_get_clean();
        $this->assertEquals($body, $contents);

        /**
         * This may not work righ tnow.
         * I think that the headers can only be set while processing a HTTP request
         */
        $this->assertFalse(headers_sent());
        $sentHeaders = headers_list();
        if (count($sentHeaders) > 0) {
            foreach ($headers as $name => $value) {
                $this->assertTrue(in_array(sprintf('%s: %s', $name, $value), $sentHeaders));
            }
        }
    } // end function testSendRawResponse

    /**
     * Send a 404 
     * @param void
     * @return void
     * @group all
     * @covers Cumula\Response::sendRawResponse
     **/
    public function testSendRawResponse404() {
        $body = uniqid('body_');
        $_SERVER['SERVER_PROTOCOL'] = uniqid('protocol_');

        ob_start();
        $this->response->sendRawResponse(array(), $body, 404);
        $contents = ob_get_clean();

        $this->assertEquals($body, $contents);


        $sentHeaders = headers_list();
        if (count($sentHeaders) > 0) {
            foreach ($sentHeaders as $header) {
                $this->assertTrue(stristr($header, '404 Not Found'));
            }
        }
    } // end function testSendRawResponse404

    /**
     * Test the send302 method
     * @param string $sendUrl
     * @return void
     * @group all
     * @covers Cumula\Response::send302
     * @dataProvider send302DataProvider
     **/
    public function testSend302($sendUrl, $expected = NULL) {

        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['HTTP_HOST'] = 'myhost';

        $this->response->send302($sendUrl);

        $this->assertEquals(302, $this->response->response['status_code']);
        $expected = ($expected == NULL ? $sendUrl : $expected);
        $this->assertEquals($expected, $this->response->response['headers']['Location']);
    } // end function testSend302

    /**
     * Test the Response::send method
     * @param void
     * @return void
     * @group all
     * @covers Cumula\Response::send
     **/
    public function testSend() {
        $content = uniqid('content_');
        $this->repsonse->response['content'] = $content;

        global $response_prepare, $response_send;
        $this->response->addEventListener('response_prepare', function() { global $response_prepare; $response_prepare = TRUE; });
        $this->response->addEventListener('response_send', function() { global $response_send; $response_send = TRUE; });

        $this->response->send();

        $this->assertTrue($response_prepare && $response_send);
    } // end function testSend

    /**
     * Data Provider for testSend302
     * @param void
     * @return array
     * @author Craig Gardner <craig@seabourneconsulting.com>
     **/
    public function send302DataProvider() {
        return array(
            'with scheme' => array('http://www.google.com'),
            'without scheme' => array('/path/to/something', 'http://myhost/path/to/something'),
        );
    } // end function send302DataProvider
} // end class Test_Response extends Test_BaseTest
