<?php
namespace Cumula\Components\CLIStream;

use \Cumula\Component\BaseComponent  as BaseComponent;

class CLIStream extends BaseComponent {
	public function __construct() {
		parent::__construct();
		A('Application')->bind('GatherStreams', array(
			'cli' => $this,
		));
	}
	
	public function install() {
		A('ComponentManager')->registerStartupComponent($this);
	}
	
	public function startup() {
		A('Router')->bind('GatherRouteTypes', array(
			'>' => " ",
		));
	}
	
	public function processRequest() {
		global $argv, $argc;
		if(isset($argv)) {
			A('Request')->fullPath = $argv[0];
			array_shift($argv);
			A('Request')->path = ">".implode(' ', $argv);
			A('Request')->requestIp = null;
			A('Request')->params = array();
			
			A('Renderer')->bind('GatherRenderers', array(
				"renderCLI" => array($this, 'renderCLI'),
				"renderDefault" => array($this, 'renderCLI'),
				"renderCLIStream" => array($this, 'renderCLIStream'),
				"renderNotFound" => array($this, 'renderNotFound'),
			));
			
			if(!isset(A('Renderer')->buffer['cli']))
				A('Renderer')->buffer['cli'] = '';
			return true;
		}
	}
	
	public function processResponse() {
		$response = A('Response');
		$response->content = A('Renderer')->buffer['cli']."\n";
	}
	
	public function renderCLI($output) {
		A('Renderer')->buffer['cli'] .= $output;
		return $output;
	}
	
	public function renderCLIStream($callback) {
		//do something
	}
	
	public function renderNotFound() {
		A('Renderer')->buffer['cli'] .= "Command Not Found";
	}
 }