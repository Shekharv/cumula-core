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
	public $stream;

	public function __construct() {
		parent::__construct();
		A('Application')->bind('BootStartup', array($this, 'startup'));
	}
	
	public function startup() {
		$h = null;
		$streams = A('Application')->getStreams();
		foreach($streams as $stream => $handler) {
			if(call_user_func(array($handler, 'processRequest'))) {
				$this->stream = $stream;
				$h = $handler;
			}
		}
		if($h)
			A('Response')->bind('ResponsePrepare', array($h, 'processResponse'));
	}
}
