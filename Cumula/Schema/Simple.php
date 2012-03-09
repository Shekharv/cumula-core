<?php
namespace Cumula\Schema;

/**
 * Simple Schema Class
 * @package Cumula
 * @subpackage Core
 */
class Simple extends \Cumula\Base\Schema {
    /**
     * Constructor
     * @param string $name
     * @param string $id
     * @param array $fields
     */
	public function __construct($fields, $id, $name) {
        parent::__construct($fields, $id, $name);
	}
}

