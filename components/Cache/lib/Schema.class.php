<?php

namespace Cache;

/**
 * Cache Schema
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 **/
class Schema extends \Cumula\BaseSchema 
{
	/**
	 * Constructor
	 * @param void
	 * @return void
	 **/
	public function __construct() 
	{
		// Nothing to do
	} // end function __construct
	
	/**
	 * Get the name of the schema
	 * @param void
	 * @return string
	 **/
	public function getName() 
	{
		return 'cache';
	} // end function getName

	/**
	 * Get a definition of the fields
	 * @param void
	 * @return array
	 **/
	public function getFields() 
	{
		return array(
			'cid' => array(
				'type' => 'string',
				'required' => TRUE,
				'unique' => TRUE,
			),
			'data' => array(
				'type' => 'text'
			),
			'expire' => array(
				'type' => 'integer',
			),
			'created' => array(
				'type' => 'integer',
			),
		);
	} // end function getFields

	/**
	 * Get the ID field of the schema
	 * @param void
	 * @return string
	 **/
	public function getIdField() 
	{
		return 'cid';
	} // end function getIdField
} // end class Schema extends SimpleSchema {
