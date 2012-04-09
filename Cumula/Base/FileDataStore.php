<?php
namespace Cumula\Base;

abstract class FileDataStore extends DataStore {
	static public $requiredConfig = array('filename', 'sourceDir');
	protected $_filename;
	protected $_sourceDir;
	
	protected function _dataStoreFile() {
		return $this->_config['sourceDir'].'/'.$this->_config['filename'];
	}
}