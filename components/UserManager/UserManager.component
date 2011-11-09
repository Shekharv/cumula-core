<?php
namespace UserManager;
use \Cumula\BaseComponent as BaseComponent;

require_once 'lib/BasicUserSchema.class.php';

class UserManager extends BaseComponent {
	protected $_domains;
	protected $_paths;
	
	protected $_userStore;
	
	public function __construct() {
		parent::__construct();
		$this->addEvent('register_auth_domain');
		$this->_domains = array();
		$this->_paths = array();
		
		$schema = new BasicUserSchema();
		$this->_userStore = new \SqliteDataStore\SqliteDataStore($schema, array('source_directory' => DATAROOT, 'filename' => 'user_store'));
	}
	
	public function startup() {
		$this->addEventListenerTo('Application', 'boot_preprocess', 'checkRoutes');
		$this->addEventListenerTo('Router', 'router_collect_routes', 'sendRoutes');
		$this->_userStore->connect();
	}
	
	public function sendRoutes($route, $dispatcher) {
		$routes = array(
			'/user/login' => array(&$this, 'login'),
			'/user/logout' => array(&$this, 'logout'),
			'/user/register' => array(&$this, 'register'),
			'/user/authenticate' => array(&$this, 'authenticate'),
		);
		$dispatcher->addRoutes($routes);
	}
	
	public function registerAuthDomain($domain, $args) {
		$paths = $args['paths'];
		foreach($paths as $path) {
			$this->_paths[$path] = $domain;
		}
		$this->_domains[$domain] = $args;
	}
	
	public function checkRoutes($event, $dispatcher, $request, $response) {
		$this->dispatch('register_auth_domain');
		$domain = $this->_parseRoutes($request);
		if($domain) {
			$this->_logInfo('setting domain to '.$domain);
			if($app = \Cumula\Application::instance()) {
				$router = \Cumula\Router::instance();
				$app->removeEventListener('boot_process', array($router, 'processRoute'));
				$this->addEventListenerTo('Application', 'boot_process', 'process'.$domain);
				\I('Session')->domain = $domain;
				\I('Session')->returnUrl = $request->path;
			}
		}
	}
	
	public function __call($name, $args) {
		if(strlen($name) > 7 && substr($name, 0, 7) == 'process') {
			$name = str_replace('process', '', $name);
			if(array_key_exists($name, $this->_domains))
				$this->authDomain($name);
		}
		return;
	}
	
	public function authDomain($domain) {
		//Check if the session $user var is set
		if(isset(\I('Session')->user)) {
			//if found, process the route as is without doing anything
			\I('Router')->processRoute('boot_proces', \I('Application'), \I('Request'), \I('Response'));
		} else {
			//if not, redirect the user to the login page, pass the original path and domain in the session for the user
			$this->redirectTo('/user/login');
		}
	}
	
	public function login() {
		$this->render();
	}
	
	public function logout() {
		unset(\I('Session')->user);
		\I('Session')->notice = 'You have been logged out.';
		$this->redirectTo('/');
	}
	
	public function createUser($domain, $login, $credential) {
		$user = $this->_userStore->newObj();
		$user->username = $login;
		$user->domain = $domain;
		$user->password = md5($credential);
		if(!$this->_userStore->query(array('domain' => $domain, 'username' => $login)))
			return $this->_userStore->create($user);
		else 
			return false;
	}
	
	public function deleteUser($domain, $login) {
		$user = $this->_userStore->query(array('domain' => $domain, 'username' => $login));
		if($user)
			$this->_userStore->delete($user);
	}
	
	public function authenticate($route, $router, $args) {
		$domain = \I('Session')->domain;
		$returnUrl = \I('Session')->returnUrl;

		//get the username and credentail from auth
		if($this->_userStore->query(array('domain' => $domain, 'username' => $args['username'], 'password' => md5($args['password'])))) {
			$this->redirectTo($returnUrl);
			\I('Session')->user = new \stdClass();
		} else {
			\I('Session')->warning = 'Username or Password Incorrect.';
			$this->redirectTo('/user/login');
		}
		//check the username/auth against the domain, pulling from the specified datastore
		
		//if valid, redirect the user to the original path
		//if invalid, redirect the user to a login required page
	}
	
	protected function _parseRoutes($request) {
		
		//Trim off forward slash
		$path = substr($request->path, 1, strlen($request->path));

		$this->_logInfo("path is ".$path);

		//Trim off trailing slash
		if(substr($path, strlen($path)-1, strlen($path)) == '/')
			$path = substr($path, 0, strlen($path)-1);	
			
		//Generate array of url segments
		$segments = explode('/', $path);
		foreach($this->_paths as $route => $domain) {
			if($route == '/' && ($path == '/' || $path == '')) {
				return $domain;
			}
			
			//Check if the event is a route, if not continue
			if(substr($route, 0, 1) != '/')
				continue;

			//Extract route segemtns
			$route_segments = explode('/', substr($route, 1, strlen($route)));
			$match = false;
			$args = array();

			if(count($segments) != count($route_segments))
				continue;

			//Iterate through all URL segments
			for($i = 0; $i < count($segments); $i++) {
				$segment = $segments[$i];
				$route_segment = $i < count($route_segments) ? $route_segments[$i] : false;

				//If the route is shorter than the url, go to next route
				if(!$route_segment) {
					$match = false;
					break;
				}
				
				//Route segment is a variable, save for parsing
				if(substr($route_segment, 0, 1) == '$') {
					$match = true;
				} else if($route_segment == $segment) {
					//Route segment and segment match, go to next iterator
					$match = true;
				} else {
					$match = false;
					break;
				}

				//If route is wildcard the rest of the url will match
				if($route_segment == '*') {
					$match = true;
					break;
				}
			}

			//The urls match, so we call the passed handler function, passing in the args
			if($match) {
				return $domain;
			}
		}
		return false;
	}

  /**
   * Implementation of the getInfo method
   * @param void
   * @return array
   **/
  public static function getInfo() {
    return array(
      'name' => 'User Manager',
      'description' => 'UI for managing users',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}
