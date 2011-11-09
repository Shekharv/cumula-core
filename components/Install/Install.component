<?php
namespace Install;
use \Cumula\BaseComponent as BaseComponent;

class Install extends BaseComponent {
	public function __construct() {
		parent::__construct();
	}
	
	public function startup() {
		$this->addEventListenerTo('Router', 'router_collect_routes', 'handleRoutes');
	}
	
	public function handleRoutes($route, $router) {
		$router->addRoutes(array('/install' => array(&$this, 'startInstall'),
								'/' => array(&$this, 'startInstall'),
								'/install/setup_user' => array(&$this, 'setupUser'),
								'/install/system_check' => array(&$this, 'systemCheck'),
								'/install/save_user' => array(&$this, 'saveUser'),
								'/install/finished' => array(&$this, 'finished'),
								'/install/complete' => array(&$this, 'complete')));
	}
	
	public function startInstall() {
		$this->_setTemplate();
		$this->render();
	}
	
	public function setupUser() {
		$this->_setTemplate();
		$this->render();
	}
	
	public function systemCheck() {
		$this->_setTemplate();
		
		$this->perms = array();
		$readable_files = array(CONFIGROOT, APPROOT, COMPROOT, DATAROOT, PUBLICROOT, ASSETROOT, CONTRIBCOMPROOT);
		$writable_files = array(CONFIGROOT, DATAROOT, PUBLICROOT, ASSETROOT, CONTRIBCOMPROOT);
		foreach($readable_files as $file) {
			if(!isset($this->perms[$file]))
				$this->perms[$file] = TRUE;
			$this->perms[$file] = (is_readable($file) && $this->perms[$file]);
		}
		foreach($writable_files as $file) {
			if(!isset($this->perms[$file]))
				$this->perms[$file] = TRUE;
			$this->perms[$file] = (is_writable($file) && $this->perms[$file]);
		}
		$this->rewrite = $this->_modRewriteCheck();
		$this->render();
	}
	
	protected function _modRewriteCheck() {
		 if( ! function_exists('apache_get_modules') ){ return false; }
		 if(in_array('mod_rewrite',apache_get_modules())) return true;
	}
	
	
	public function saveUser($route, $router, $args) {
		$um = \I('UserManager');
		if($args['password'] == $args['passconf']) {
			$um->createUser('admin_interface', $args['username'], $args['password']);
			$this->redirectTo('/install/finished');
		} else {
			\I('Session')->warning = 'Password and confirmation don\'t match!';
			$this->redirectTo('/install/setup_user');
		}
	}
	
	public function finished() {
		$this->_setTemplate();
		\I('ComponentManager')->uninstallComponent('Install');
		$this->render();
	}
	
	protected function _setTemplate() {
		$this->addEventListenerTo('Templater', 'templater_prepare', function($event, $templater) {
			$templater->setTemplateDir(COMPROOT.'Install/template/');
		});
	}
	

}