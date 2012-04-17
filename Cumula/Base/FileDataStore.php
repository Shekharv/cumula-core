<?php
namespace Cumula\Base;

abstract class FileDataStore extends DataStore {
	protected $_filename;
	protected $_sourceDir;

	public function requiredConfig() {
		return array_merge(array('filename', 'sourceDir'), parent::requiredConfig());
	}
	
	protected function _dataStoreFile() {
		return $this->_config['sourceDir'].'/'.$this->_config['filename'];
	}
}