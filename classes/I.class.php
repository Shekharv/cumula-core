<?php

class DummyComponent {
	public function __call($name, $args) {
		return $this->_triggerError();
	}
	
	public function __get($name) {
		return $this->_triggerError();
	}
	
	private function _triggerError() {
		trigger_error('You called an instance which doesnt exist');
	}
}

function I($component) {
	$loader = \Cumula\Autoloader::instance();
	if($abs = $loader->absoluteClassName($component)) {
		return $abs::instance();
	} else {
		return new DummyComponent();
	}
}

