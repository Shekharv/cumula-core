<?php
namespace Cumula\DataStore;

class ReadOnlyDataStore extends \Cumula\Base\DataStore {

	public function get($args) {
		$obj = $this->query($args);
		if ($obj) {
			$obj = $obj[0];
		}
		return $obj;
	}
	
	public function create($obj) {
		return new Exception("Read only datastore");
	}

	public function update($obj) {
		return new Exception("Read only datastore");
	}

	public function destroy($obj) {
		return new Exception("Read only datastore");
	}

	public function install() {
		return new Exception("Read only datastore");
	}

	public function uninstall() {
		return new Exception("Read only datastore");
	}

	public function translateFields($fields) {
		return new Exception("Read only datastore");
	}

	public function lastRowId() {
		return new Exception("Read only datastore");
	}

	// Not clear the abstract class is worth this loss/annoyance,
	//   missing somethign about PHP?

	public function query($args, $order=null, $limit=null, $start=null) {
		
	}

	public function recordExists($obj) {
		
	}
	
	public function connect() {
		
	}

	public function disconnect() {
		
	}
}