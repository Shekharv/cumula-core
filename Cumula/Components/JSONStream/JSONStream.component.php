<?php
namespace Cumula\Components\JSONStream;

class JSONStream extends \Cumula\Base\Stream {
	public function renderJSON($obj, $args = array()) {
		A('Renderer')->useTemplate = false;
		A('Response')->data['headers']['content-type'] = 'application/json';
		if(is_string($obj) && file_exists($obj)) {
			$obj = (object)array('return' => $this->renderHTML($obj, $args));
		}
		A('Response')->content = json_encode($obj);
	}	
	
	public function processRequest() {
		parent::processRequest();		
		if((isset($_SERVER['accept']) && $_SERVER['accept'] == 'application/json') || (isset($_SERVER['PATH_INFO']) && strstr($_SERVER['PATH_INFO'], '.json'))) {
			A('Request')->path = str_replace('.json', '', $_SERVER['PATH_INFO']);
			A('Renderer')->bind('GatherRenderers', array(
				"renderJSON" => array($this, 'renderJSON'),
				'renderDefault' => array($this, 'renderJSON')
			));
			return true;
		}
	}
}