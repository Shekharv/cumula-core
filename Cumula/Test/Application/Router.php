<?php

use \Cumula\Application\Router as Router;

class Test_Router extends \Cumula\Test\Base {
    private $router;
    
    public function setUp() {
			parent::setUp();
			$this->router = new Router();
    } 

	public function testGatherRoutes() {
		$this->assertIsBound(A('Application'),
							 'BootPreprocess',
							 $this->router);
		$testFunction = function() {};
		$this->router->bind('GatherRoutes', array(
								'/test' => $testFunction,
								'/test/other' => $testFunction,
								));
		$this->router->collectRoutes(1);
		$this->assertContains('/test/other', array_keys($this->router->getRoutes()));
	}
} 
