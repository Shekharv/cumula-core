<?php
namespace GDocsAPIDataStore;
use \Cumula\BaseSchema as BaseSchema;

/**
 * Simple Schema Class
 * @package Cumula
 * @subpackage Core
 */
class GDocsSchema extends BaseSchema {
	public function __construct() {
		parent::__construct();
	}

    /**
     * Get the name of the schema
     * @return string
     */
	public function getName() {
		return 'docs';
	}

    /**
     * Get the Fields for the Schema
     * @return array
     */
	public function getFields() {
		return array("title" => "string", 
					 "id" => "string", 
					 "slug" => "string", 
					 "content" => "string", 
					 "contentType" => "string",
					 "author" => "string",
					 "published" => "datetime",
					 "category" => "array");
	}
	
    /**
     * Get the ID Field for the Schema
     * @param void
     * @return string
     */
	public function getIdField() {
		return 'id';
	}
}

