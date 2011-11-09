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
 * BaseAPIDataStore Class
 *
 * Abstract Class for all API derived Data Stores.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */

abstract class BaseAPIDataStore extends BaseDataStore {

	public function __construct($schema, $config_values) {
		parent::__construct($schema, $config_values);
	}

	public function create($obj) {
		
	}
	
	public function update($obj) {
		
	}
	
	public function destroy($obj) {
		
	}
	
	public function query($args, $order = array(), $limit = array()) {
		
	}
	
	public function install() {
		
	}
	
	public function uninstall() {
		
	}
	
	public function translateFields($fields) {
		
	}
	
	public function recordExists($id) {
		
	}
	
	public function lastRowId() {
		
	}
}