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
	public $response = array('headers' => array(), 
							   'content' => '',
							   'status_code' => 200,
							   'data' => array());
	
	/**
	 * Constructor
	 * 
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();
		$this->addEventListenerTo('Cumula\\Application', 'boot_shutdown', 'send');
		$this->addEvent('response_prepare');
		$this->addEvent('response_send');
	}
	
	/**
	 * Dispatches the response to the browser
	 * 
	 * @return unknown_type
	 */
	public function send() {
		$this->dispatch('response_prepare');
		$this->sendRawResponse($this->response['headers'], $this->response['content'], $this->response['status_code']);
		$this->dispatch('response_send');
	}
	
	public function send404() {
		$this->response['status_code'] = 404;
		$this->sendRawResponse($this->response['headers'], $this->response['content'], $this->response['status_code']);
		exit;
	}
	
	public function send405() {
		$this->response['status_code'] = 405; //Change Denied
		$this->response['headers']['Pragma'] = 'no-cache';
		$this->sendRawResponse($this->response['headers'], 'Invalid Change', $this->response['status_code']);
		exit;
	}
	
	public function send302($url) {
    if (FALSE === stripos($url, 'http')) {
      $protocol = ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'))
        ? 'https' : 'http';
      if ($url{0} != '/') $url = '/'.$url;
      $url = $protocol.'://'.$_SERVER['HTTP_HOST'].$url;
    }
		$this->response['headers']['Location'] = $url;
		$this->response['status_code'] = 302;
	}
	
	/**
	 * Sends the raw response to the browser.
	 * 
	 * @param $headers array An array of key value pairs of header: values
	 * @param $body string The body of the response
	 * @param $code integer The HTTP response code
	 * @return unknown_type
	 */
	public function sendRawResponse($headers, $body, $code) {
		if ($code == 404) {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			echo $body;
			return;
		}
		foreach($headers as $key => $value) {
			$this->_sendHeader($key, $value, $code);
		}
		echo $body;
	}
	
	private function _sendHeader($header, $value, $status_code = null) {
		header("$header: $value", true, $status_code);
	}
}
