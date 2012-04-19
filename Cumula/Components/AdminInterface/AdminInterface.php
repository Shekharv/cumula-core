<?php
namespace Cumula\Components\AdminInterface;

class AdminInterface extends \Cumula\Base\Component {
	protected $_fields;
	
	public function __construct() {
		parent::__construct();
		A('AliasManager')->setDefaultAlias('AdminInterface', get_called_class());
		$this->addEvent('GatherAdminPages');
	}
	
	public function startup() {		
		A('Application')->bind('BootPreprocess', array($this, 'gatherAdminPages'));
		A('Router')->bind($this->getConfigValue('basePath', '/admin'), array($this, 'index'));
		A('Router')->bind($this->getConfigValue('basePath', '/admin').'/save-settings', array($this, 'saveAdminPage'));
		
		
		$this->bind('GatherAdminPages', array(
			'Admin Interface' => array(
				'config' => $this->config,
				'fields' => array(
					'basePath' => array(
						'type' => 'string',
						'title' => 'Admin Interface Base Path',
						'value' => $this->getConfigValue('basePath', '/admin')
					)
				)
			)
		));
	}
	
	public function gatherAdminPages() {
		$menus = array();
		$pages = array();
		$fields = array();
		$this->dispatch('GatherAdminPages', function($page) use (&$pages) {
			$pages = array_merge($pages, $page);
		});
		foreach($pages as $page => $config) {
			$url = $this->_buildUrl($page);
			$title = isset($config['title']) ? $config['title'] : $page;
			if(isset($config['parent'])) {
				if(!isset($menus[$config['parent']]))
					$menus[$config['parent']] = array();
				$menus[$config['parent']][$title] = $this->completeUrl($url);
			} else
				$menus[$title] = $this->completeUrl($url);
			A('Router')->bind($url, array($this, 'adminPage'));
			$fields[$page] = $config;
		}
		$this->_fields = $fields;
		
		$this->renderBlock($this->renderView('mainMenu.tpl.php', array('menus' => $menus)), 'adminMenu');
	}
	
	public function adminPage($route, $router, $args) {
		$page = $this->_buildPage($route);
		$this->render(array(
			'title' => $page, 
			'page' => $this->_fields[$page],
			'fh' => A('FormHelper'),
			'savePath' => $this->getConfigValue('basePath', '/admin').'/save-settings',
			'startPath' => $route
		));
	}
	
	public function saveAdminPage($route, $router, $args) {
		$page = $this->_buildPage($args['setting-page']);
		$fields = $this->_fields[$page]['fields'];
		$config = $this->_fields[$page]['config'];
		$vals = array();
		foreach($fields as $field => $fieldConfig) {
			if(isset($args[$field])) {
				$config->setConfigValue($field, $args[$field]);
				$vals[$field] = $args[$field];
			} else if($fieldConfig['type'] == 'checkbox') {
				$value = isset($args[$field]) ? $args[$field] : false;
				$config->setConfigValue($field, $value);
				$vals[$field] = $value;
			}
		}
		if(isset($this->_fields[$page]['callback']))
			call_user_func_array($this->_fields[$page]['callback'], $vals);
		$this->renderRedirect($this->completeUrl($this->_buildUrl($page)));
	}
	
	protected function _buildPage($route) {
		$basePath = $this->getConfigValue('basePath', '/admin');
		return ucwords(str_replace('-', ' ', str_replace($basePath."/", '', $route)));
	}
	
	protected function _buildUrl($url) {
		$basePath = $this->getConfigValue('basePath', '/admin');
		return $basePath."/".strtolower(str_replace(" ", "-", $url));
	}
	
	public function index() {
		$installedComps = count(A('ComponentManager')->getEnabledComponents());
		$this->render(array(
			'installedComps' => $installedComps, 
			'perms' => $this->_checkPerms()
		));
	}
	
	protected function _checkPerms() {
		$perms = array();
		$readable_files = array(CONFIGROOT, APPROOT, COMPROOT, DATAROOT, PUBLICROOT, ASSETROOT, CONTRIBCOMPROOT);
		$writable_files = array(CONFIGROOT, DATAROOT, PUBLICROOT, ASSETROOT, CONTRIBCOMPROOT);
		foreach($readable_files as $file) {
			if(!isset($perms[$file]))
				$perms[$file] = TRUE;
			$perms[$file] = (is_readable($file) && $perms[$file]);
		}
		foreach($writable_files as $file) {
			if(!isset($perms[$file]))
				$perms[$file] = TRUE;
			$perms[$file] = (is_writable($file) && $perms[$file]);
		}
		return $perms;
	}
	
	/**
	* Implementation of the getInfo method
	* @param void
	* @return array
	**/
	public static function getInfo() {
		return array(
			'name' => 'Administration Interface',
			'description' => 'Default Administrative interface for Cumula',
			'version' => '0.1.0',
			'dependencies' => array('UserManager', 'MenuManager', 'FormHelper'),
		);
	} // end function getInfo
}