<?php
namespace Cumula;
/**
 * Cumula
 *
 * Cumula — framework for the cloud.
 *
 * @package    Cumula
 * @version    0.1.0
 * @author     Seabourne Consulting
 * @license    MIT License
 * @copyright  2011 Seabourne Consulting
 * @link       http://cumula.org
 */

/**
 * CumulaSchema Interface
 *
 * Describes the basic Schema functions.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
interface CumulaSchema {	
	/**
	 * Returns an array of key value pairs describing the fields associated with the object stored in the schema.
	 * @return unknown_type
	 */
	public function getFields();
	
	public function setFields($fields);
	
	/**
	 * Defines the unique id field used by the schema
	 * @return unknown_type
	 */
	public function getIdField();
	
	public function setIdField($idField);
	
	public function getObjInstance();
}      