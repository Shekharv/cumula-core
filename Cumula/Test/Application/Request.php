<?php

use \Cumula\Application\Request as Request;

require_once 'Cumula/Test/Base.php';

class Test_Request extends Test_BaseTest {

	public $request;

	public function setUp() {
		parent::setUp();
		$this->request = new Request();
	} 

	public function testConstructor() {
		$this->assertEquals(null, $this->request->path);
	}

	public function testStartup() {
		$this->assertDispatches(
			A("Request"),
			'ProcessRequest',
			function ($that)  {
				$that->request->startup();
			},
			array(),
			'Cumula\Application\Request'
			);
	}

	public function testWithHTMLStream() {
		$_SERVER['PATH_INFO'] = "/test";
		$_SERVER['REQUEST_URI'] = "/base/test";
		$_SERVER['REQUEST_METHOD'] = "POST";
		$stream = new Cumula\Application\HTMLStream();

		$this->request->startup();
		$this->assertEquals("/test", $this->request->path);
		$this->assertEquals("/base/test", $this->request->fullPath);
		$this->assertEquals("POST", $this->request->method);
		$this->assertEquals("127.0.0.1", $this->request->requestIp);
	}
}
