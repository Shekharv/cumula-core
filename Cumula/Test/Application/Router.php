<?php

use \Cumula\Application\Router as Router;

require_once 'Cumula/Test/Base.php';

class Test_Router extends Test_BaseTest {
    private $router;
    
    public function setUp() {
			parent::setUp();
			$this->router = new Router();
    } 

	public function testGatherRoutes() {
		$this->assertIsBound(A('Application'),
							 'BootPreprocess',
							 $this->router);
		$this->router;
	}
} 
