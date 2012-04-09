<?php
namespace Cumula\DataStore\YAML;

class YAMLFactory extends \Cumula\Base\Component implements \Cumula\Interfaces\CumulaDataStoreFactory {
	public function get($fields, $id, $name, $config) {
		$ds = new YAML();
		if (!isset($config['source_directory'])) {
			$config['source_directory'] = DATAROOT;
		}
		$ds->setup($fields, $id, $name, $config);
		return $ds;
	}
}