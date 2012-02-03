<?php
namespace Cumula;
/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */

/**
 * Request Class
 *
 * The base class representing the HTTP request
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
final class Request extends EventDispatcher {
	public $path;
	public $arguments;
	public $requestIp;
	public $params;
	
	public function __construct() {
		parent::__construct();
		$this->init();
	}
	
	protected function init() {
		$this->path = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '';
		$this->requestIp = $_SERVER['REMOTE_ADDR'];
		$this->params = array_merge($_GET, $_POST);
		array_walk_recursive($this->params, function(&$ele, $key) {$ele = str_replace("\\\\", "\\", $ele);});
	}
}
