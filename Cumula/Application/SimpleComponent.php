<?php
namespace Cumula\Application;

class SimpleComponent extends \Cumula\Base\Component {
	public $dataStores;
    
	public function startup() {
		parent::startup();
		$this->startDataStores();
	}

	public function shutdown() {
		parent::shutdown();
		$this->stopDataStores();
	}

	public function startDataStores() {
		$this->dataStores = array();
		foreach($this->getConfigValue('dataStores') as $name => $params) {
			if (isset($params['factory'])) {
				$factory = $params['factory'];
				unset($params['factory']);
				$ds = A($factory)->get();
			} else {
				$engine = $params['engine'];
				unset($params['engine']);
				$ds = new $engine();
			}
			$ds->setup($this->schemas[$name], 'id', $name, $params);
			$ds->connect();
			$this->dataStores[$name] = $ds;
		}
	}

	public function stopDataStores() {
		foreach($this->dataStores as $name => $ds) {
			$ds->disconnect();
		}
	}
	
}
