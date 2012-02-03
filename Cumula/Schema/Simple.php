<?php
namespace Cumula;

/**
 * Simple Schema Class
 * @package Cumula
 * @subpackage Core
 */
class SimpleSchema extends BaseSchema {
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

