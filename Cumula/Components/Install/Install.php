<?php
namespace Cumula\Components\Install;

const UserManager = "\\Cumula\\Components\\UserManager\\UserManager";

class Install extends \Cumula\Application\SimpleComponent {
	public $defaultConfig = array(
		'basePath' => 'install'
		);
	public $routes = array(
		'' => 'startInstall',
		'setup_user' => 'setupUser',
		'system_check' => 'systemCheck',
		'save_user' => 'saveUser',
		'finished' => 'finished',
		);
	
	public function startup() {
		parent::startup();
		A('Router')->bind('/', array($this, 'startInstall'));
	}
		
	public function startInstall() {
		$this->render();
	}
	
	public function setupUser() {
		$this->render();
	}
	
	public function systemCheck() {
		if (is_writable(APPROOT)) {
			if (!file_exists(CONFIGROOT)) {
				mkdir(CONFIGROOT, 0775, true);
			}
			if (!file_exists(DATAROOT)) {
				mkdir(DATAROOT, 0775, true);
			}
			if (!file_exists(CONTRIBCOMPROOT)) {
				mkdir(CONTRIBCOMPROOT, 0775, true);
			}
		}
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
		$um = \A(UserManager);
		if($args['password'] == $args['passconf']) {
			$um->createUser('admin_interface', $args['username'], $args['password']);
			$this->redirectTo('/install/finished');
		} else {
			\A('Session')->warning = 'Password and confirmation don\'t match!';
			$this->redirectTo('/install/setup_user');
		}
	}
	
	public function finished() {
		$cm = \A('ComponentManager');
		$cm->installComponents($cm->getAvailableComponents());
		$cm->uninstallComponent('Cumula\Components\Install\Install');
		$cm->writeConfig();
		$this->render();
	}
}