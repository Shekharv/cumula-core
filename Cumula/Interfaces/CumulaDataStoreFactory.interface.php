<?php

namespace Cumula\Interfaces;

interface CumulaDataStoreFactory {
	public function get($fields, $id, $name, $config);
}