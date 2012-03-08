<?php
namespace GDocsAPIDataStore;

use \Cumula\BaseAPIDataStore as BaseAPIDataStore;

require_once(__DIR__.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Zend'.DIRECTORY_SEPARATOR.'Loader.php');

set_include_path(__DIR__.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR);

\Zend_Loader::loadClass('Zend_Gdata');

\Zend_Loader::loadClass('Zend_Gdata_AuthSub');

\Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

\Zend_Loader::loadClass('Zend_Gdata_Docs');

\Zend_Loader::loadClass('Zend_Gdata_App_MediaFileSource');

require_once('GDocsZendStreamMediaSource.class.php');

require_once('GDocsSchema.class.php');

class GDocsAPIDataStore extends BaseAPIDataStore {
	protected $_username = null;
	protected $_password = null;
	
	protected $_client;
	
	public function __construct(GDocsSchema $schema, $config_values) {		
		parent::__construct($schema, $config_values);
		if(!isset($config_values['username']))
			throw new Exception('Need a username');
		else
			$this->_username = $config_values['username'];
			
		if(!isset($config_values['password'])) 
			throw new Exception('Need a password');
		else
			$this->_password = $config_values['password'];
	}
	
	public function connect() {
		if($this->_username && $this->_password) {
			$httpClient = \Zend_Gdata_ClientLogin::getHttpClient($this->_username, $this->_password, \Zend_Gdata_Docs::AUTH_SERVICE_NAME);
			$this->_client = new \Zend_Gdata_Docs($httpClient);
			return $this->_client;
		} else {
			return false;
		}
	}
	
	public function disconnect() {
		$this->_client = null;
	}
	
	public function create($obj) {
		// Set the URI to which the file will be uploaded.
		$uri = \Zend_Gdata_Docs::DOCUMENTS_LIST_FEED_URI;

        // Create the media source which describes the file.
        $entry = $this->_objToMediaSource($obj);

		return $this->_parseObj($this->_client->insertDocument($entry, $uri));
	}
	
	public function update($obj) {
		$this->_client->getDocument($obj->id);
	}
	
	public function findAll() {
		$ret = array();
		$feed = $this->_client->getDocumentListFeed('http://docs.google.com/feeds/documents/private/full/-/document');
		foreach($feed->entries as $entry) {
			$ret[] = $this->_parseObj($entry);
		}
		return $ret;
	}
	
	public function query($args, $order = array(), $limit = array()) {
		if(is_string($args)) { //assume its an id and return the doc based on that
			$entry = $this->_client->getDocument($args);
			$obj = $this->_parseObj($entry);
			if($entry->content && $entry->content->src)
				$obj->content = $this->_getContent($entry->content->src);
			return $obj;
		} else if (is_array($args)) {
			foreach($args as $key => $value) {
				if($key == 'folder') {
					$ret = $this->_client->getDocumentListFeed("https://docs.google.com/feeds/documents/private/full/-/".urlencode($value));
				} else if ($key == 'folders' && $value == 'all') {
					$feed = $this->_client->getDocumentListFeed("https://docs.google.com/feeds/documents/private/full?showfolders=true");
					$ret = array();
					foreach($feed->entry as $entry) {
						foreach($entry->category as $cat) {
							if($cat->scheme == "http://schemas.google.com/g/2005#kind" && $cat->term == "http://schemas.google.com/docs/2007#folder")
								$ret[] = $entry;
						}
					}
				}
				for($i = 0; $i < count($ret); $i++) {
					$entry = $ret[$i];
					$ret[$i] = $this->_parseObj($entry);
				}
				return $ret;
			}
		}
	}
	
	protected function _objToMediaEntry($obj) {
		if(is_a($obj, "Zend_Gdata_App_Entry")) {
			return $obj;
		}
	}
	
	protected function _objToMediaSource($obj) {
		if(is_a($obj, "Zend_Gdata_App_Entry") || is_a($obj, "Zend_Gdata_App_MediaSource")) {
			return $obj;
		}
		
		$entry = new GDocsZendStreamMediaSource($obj->content);
		$entry->setSlug($obj->title);
		$entry->setContentType($obj->type);
		return $entry;
	}
	
	protected function _parseObj($entry) {
		$obj = $this->_schema->getObjInstance();
		foreach($obj as $key => $value) {
			try {
				isset($entry->$key);
			} catch (\Zend_Gdata_App_InvalidArgumentException $e) {
				continue;
			}
			$obj->$key = $entry->$key;
		}
		$obj->id = $this->_parseId((string)$entry->id);
		return $obj;
	}
	
	protected function _parseId($id) {
		$ret = str_ireplace("http://docs.google.com/feeds/documents/private/full/document%3A", "", $id);
		$ret = str_ireplace("https://docs.google.com/feeds/documents/private/full/document%3A", "", $ret);
		return $ret;
	}
	
	protected function _getContent($url, $type = 'html') {
		$ret = (string)$this->_client->get($url.'&exportFormat='.$type)->getBody();
		return $ret;
	}
}
