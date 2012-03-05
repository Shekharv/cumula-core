<?php

namespace UserManager;
use \Cumula\BaseSchema as BaseSchema;

class BasicUserSchema extends BaseSchema {

	public function getName() {
		return 'user';
	}
	
	public function getFields() {
		return array('id' => array('type' => 'integer', 'autoincrement' => true),
					'domain' => array('type' => 'string'),
					'username' => array('type' => 'string'),
					'password' => array('type' => 'string'));
	}
	
	public function getIdField() {
		return 'id';
	}
}