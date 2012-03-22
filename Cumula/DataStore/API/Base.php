<?php
namespace Cumula\DataStore\API;
use Cumula\DataStore\Base as BaseDataStore;

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

abstract class Base extends \Cumula\Base\DataStore {

	public function __construct() {
		parent::__construct();
	}

	public function create($obj) {
		
	}
	
	public function update($obj) {
		
	}
	
	public function destroy($obj) {
		
	}
	
	public function query($args, $order = null, $limit = null, $start = null) {
		
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