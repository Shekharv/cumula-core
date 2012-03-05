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
require_once BASE_DIR.'/classes/Request.class.php';

/**
 *  Test Class
 * @package Cumula
 * @subpackage Core
 **/
class Test_Request extends Test_BaseTest {

	public function createInstance() {
		return new \Cumula\Request();
	}

	public function testProperties() {
		$_SERVER['PATH_INFO'] = "/home/";
		$_SERVER['REQUEST_URI'] = "/cumula/home/";
		$_GET["q"] = "search";
		$_POST["name"] = "John";

		$request = $this->createInstance();

		$this->assertEquals($request->path, "/home/");
		$this->assertEquals($request->fullPath, "/cumula/home/");
		$this->assertEquals($request->requestIp, "127.0.0.1");
		$this->assertEquals($request->params, array(
								'q' => 'search',
								'name' => 'John'
								),
							var_export($request->params, true));
	}
}
