<?php
namespace Cumula\Base;

abstract class Stream extends Component {
	
	public function __construct() {
		parent::__construct();
		A('Application')->bind('GatherStreams', array(
			$this->getStreamName() => $this,
		));
		
		A('Request')->bind('ProcessRequest', array($this, 'processRequest'));
	}
	
	public function processResponse() {
		
	}
	
	public function processRequest() {
		A('Response')->bind('Prepare'.$this->getStreamName(), array($this, 'processResponse'));
	}
	
	public function getStreamName() {
		return get_called_class();
	}
	
	public function install() {
		A('ComponentManager')->registerStartupComponent($this);
	}
}