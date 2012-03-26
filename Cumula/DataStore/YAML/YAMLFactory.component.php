<?php
namespace Cumula\DataStore\YAML;

class YAMLFactory extends \Cumula\Base\Component implements \Cumula\Interfaces\CumulaDataStoreFactory {
	public function get($fields, $id, $name, $config) {
		$ds = new YAML();
		$ds->setup($fields, $id, $name, $config);
		return $ds;
	}
}