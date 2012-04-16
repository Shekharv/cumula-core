<?php
namespace Cumula\Base;

const CACHE = '\\Cumula\\Components\\Cache\\Cache';

class DataService extends \Cumula\Application\EventDispatcher {
	protected $_config;
	
	public function __construct($config) {
		parent::__construct();
		if(!isset($config['cacheExpire']))
			$config['cacheExpire'] = '5 minutes';
			
		$this->_config = $config;
	}
	
	//$method: the HTTP post method
	//$url: the full url path, including query parameters
	//$params: the array of query parameters
	//$header: an array of key/value headers
	public function call($method, $url, $headers = array(), $values = array()) {
		$cache = A(CACHE)->get($url);
		if($cache)
			return $cache;
			
	    $ch = curl_init();
	
		$newHeaders = array();
		
		foreach($headers as $key => $value) {
			$newHeaders = "$key: $value";
		}
		
	    curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $newHeaders);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if(strtoupper($method) == 'POST')
			curl_setopt($ch, CURLOPT_POSTFIELDS, $values);
	    $output = curl_exec($ch);
		$header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
		
		$ret = array('response' => $output, 'code' => $header);
		
		A(CACHE)->set($url, $ret, array('expire' => $this->_config['cacheExpire']));
	    return $ret;
	}
	
	public function get($url, $params = array(), $headers = array()) {
		return $this->call('get', $this->parameterizeUrl($url, $params), $headers);
	}
	
	public function post($url, $values, $params = array(), $headers = array()) {
		return $this->call('post', $this->parameterizeUrl($url, $params), $headers, $values);
	}
	
	public function delete($url, $params = array(), $headers = array()) {
		return $this->call('delete', $this->parameterizeUrl($url, $params), $headers);
	}
	
	public function parameterizeUrl($url, $params) {
		if(!strstr($url, "?"))
			$url .= "?";
		if(!empty($params)) {
			foreach($params as $key => $value) {
				$url .= "$key=".urlencode($value)."&";
			}
		}
		return $url;
	}
	
	public function connect() {
		
	}
	
	public function disconnect() {
		
	}
}