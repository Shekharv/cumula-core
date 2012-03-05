<?php

class DummyComponent {
	public function __call($name, $args) {
		return $this->_triggerError();
	}
	
	public function __get($name) {
		return $this->_triggerError();
	}
	
	private function _triggerError() {
		trigger_error('You called an instance which doesn\'t exist');
	}
}

function I($component) {
	$loader = \Cumula\Autoloader::instance();
	if($abs = $loader->absoluteClassName($component)) {
		$app = \Cumula\Application::instance();
		if($app)
			$app->dispatch('InstanceAccessed', array($abs));
		return $abs::instance();
	} else {
		return new DummyComponent();
	}
}

