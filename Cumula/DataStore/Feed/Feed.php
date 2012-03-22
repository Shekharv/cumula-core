<?php
namespace Cumula\DataStore\Feed;

require_once dirname(__FILE__) . '/lib/SimplePie.compiled.php';

class Feed extends \Cumula\Base\DataStore {
	private $_storage;
	
	public function __construct() {
		parent::__construct();
		$this->_storage = SimplePie()
	}

	public function setup($fields, $id, $name, $configValues) {
		parent::setup($fields, $id, $name, $configValues);
		$this->setConfig($configValues);
	}

	public function setConfig($configValues) {
		$this->_storage->set_feed_url($configValues['url']);
	}

	public function connect() {
		$this->_storage->init();
	}

	public function create($obj) {
		return false;
	}

	public function query($args, $order = null, $limit = null) {
		$items = $this->_storage->get_items(); // start, $limit
		$ret = array();
		foreach($items as $item) {
			$obj = $this->newObj();
			// TODO: fixed schema
			$obj->title = $item->get_title();
			$obj->url = $item->get_link(0);
			$ret[] = $obj;
		}
		return $ret;
	}
}