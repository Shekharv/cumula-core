<?php
namespace Cumula\Application;

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
 * Response Class
 *
 * The base class representing the HTTP response.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
class Response extends EventDispatcher {
	/**
	 * The raw response object, including any headers, the content and the status code
	 * 
	 * @var array
	 */
	public $content = '';
	public $data = array();
	
	/**
	 * Constructor
	 * 
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();
		A('Application')->bind('BootShutdown', array($this, 'send'));
		$this->addEvent('ResponsePrepare');
		$this->addEvent('ResponseSend');
	}
	
	/**
	 * Dispatches the response to the browser
	 * 
	 * @return unknown_type
	 */
	public function send() {
		$streams = A('Application')->getStreams();
		$this->dispatch('ResponsePrepare');
		$this->dispatch('ResponseSend');
		echo $this->content;
	}
}
