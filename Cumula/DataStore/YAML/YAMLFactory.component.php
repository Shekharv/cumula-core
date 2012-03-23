<?php
namespace Cumula\DataStore\YAML;

class YAMLFactory extends \Cumula\Base\Component implements \Cumula\Interfaces\CumulaDataStoreFactory {
	public function get() {
		return new YAML();
	}
}