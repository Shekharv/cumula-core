<?php

namespace Cumula;

class Renderer extends EventDispatcher {
	protected $_renderers;
	public $buffer = array();
	public $useTemplate = true;
	
	public function __construct() {
		parent::__construct();
		
		$this->addEvent('GatherRenderers');
		
		A('Application')->bind('BootPrepare', array($this, 'gatherRenderers'));
	}
	
	public function gatherRenderers() {
		$renderers = array();
		$this->dispatch('GatherRenderers', function($renderer) use (&$renderers) {
			$renderers = array_merge($renderer, $renderers);
		});
		$this->_renderers = $renderers;
	}
		
	public function getRenderers() {
		return $this->_renderers;
	}	
		
	public function renderBlock($content, $block, $config = array()) {
		if($block)
			A('Renderer')->buffer[$block] = array('data' => $content, 'config' => $config);
		return $content;
	}
	
	public function __call($name, $args) {
		if(in_array($name, array_keys($this->_renderers))) {
			return call_user_func_array($this->_renderers[$name], $args);
		}
	}	
}