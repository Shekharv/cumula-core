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
	public $fullPath;
	public $arguments;
	public $requestIp;
	public $params;
	public $cli;

	public function __construct() {
		global $argv, $argc;
		parent::__construct();
		$this->cli = isset($argv);
		$this->init();
	}
	
	protected function init() {
		if(!$this->cli) {
			$this->path = array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : '';
			$this->requestIp = $_SERVER['REMOTE_ADDR'];
			$this->fullPath = $_SERVER['REQUEST_URI'];
			$this->params = array_merge($_GET, $_POST);
			array_walk_recursive($this->params, function(&$ele, $key) {$ele = str_replace("\\\\", "\\", $ele);});
		} else {
			global $argv, $argc;
			$this->path = implode(' ', $argv);
			$this->requestIp = null;
			$this->fullPath = $argv[0];
			$this->params = array();
		}
	}
}
