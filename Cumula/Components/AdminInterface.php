<?php
namespace AdminInterface;
use Cumula\BaseComponent as BaseComponent;
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
 * AdminInterface Component
 *
 * Provides a GUI interface for administering the Cumula framework.  Through the AdminPage class, components
 * can use the AdminInterface to quickly expose simple settings.  Settings are displayed to the user and automatically
 * saved back to the component config.
 *
 * ### Events
 * The Admin interface declares the following events:
 *
 * #### ADMIN_COLLECT_SETTINGS_PAGES
 * Dispatched when the AdminInterface is ready to receive admin pages.  Implementing listeners should 
 * then use either AdminInterface#addAdminPage or AdminInterface#addAdminMenuItem to register pages in the AdminInterface.
 *
 * **Args**:
 * None.
 *
 *
 * @package		Cumula
 * @subpackage	AdminInterface
 * @author     Seabourne Consulting
 */
class AdminInterface extends BaseComponent {
	protected $_pages;
	protected $_menuItems;
	
	/**
	 * Constructor.  Initializes the protected properties _pages and _menuItems.
	 *
	 **/
	public function __construct() {
		parent::__construct();
		$this->_pages = array();
		$this->_menuItems = array('/admin' => 'Admin Home');
		$this->stats = array();
	}
	
	/**
	 * Startup function.  Called on COMPONENTMANAGER_STARTUP_COMPLETE.
	 *
	 * @param   string  The name of the event.
	 * @param	ComponentManager	The ComponentManager instance.
	 *
	 **/
	public function startup() {

		/**
		 * If we add parameters to the method, we get errors saying that the method definition
		 * does not match that of BaseComponent::startup()
		 */
		$args = func_get_args();
		$event = isset($args[0]) ? $args[0] : NULL;
		$dispatcher = isset($args[1]) ? $args[1] : NULL;

		$this->addEventListenerTo('Application', 'boot_prepare', 'dispatchCollectEvent');
		
		// bind to the route collector event
		$this->addEventListenerTo('Router', 'router_collect_routes', 'collectAdminPages');
		
		// bind to the menu collector event
		$this->addEventListenerTo('MenuManager', 'menu_collect_menus', 'sendMenus');
		
		//Initialize stats for display on homepage.
		$this->stats['installed_components'] = count($dispatcher->getEnabledComponents());

		$this->addEventListenerTo('Autoloader', 'event_autoload', 'autoload');

		$this->addEventListener('admin_collect_settings_pages', array(\Cumula\SystemConfig::instance(), 'setupAdminPages'));
		
		$this->addEventListenerTo('UserManager', 'register_auth_domain', function($event, $dispatcher) {
			$dispatcher->registerAuthDomain('admin_interface', array('paths' => array('/admin', '/admin/*')));
		});
	}

	/**
	 * Event Handler for Autoloader::EVENT_AUTOLOAD
	 * @param string $event Name of the event that was dispatched
	 * @param Cumula\Autoloader $dispatcher Instance of the Object that dispatched the event
	 * @param string $className Name of the class being loaded
	 * @return void
	 **/
	public function autoload($event, $dispatcher, $className) 
	{
		return array(
			'AdminInterface\\AdminPage' => realpath(dirname(__FILE__)) .'/lib/AdminPage.class.php',
		);
	} // end function autoload
	
	/**
	 * Listener for the BOOT_PREPARE event.  Dispatches the ADMIN_COLLECT_SETTINGS_PAGES event to listeners.  
	 *
	 * @param   string  The name of the event.
	 * @param	Application	The Application instance.
	 * @param	Request	The current Request instance
	 * @param	Response	The current Response instance.
	 *
	 **/
	public function dispatchCollectEvent($event, $dispatcher, $request, $response) {
		//only dispatch the event if the request is for one of the admin routes.
		if (strstr($request->path, '/admin')) {	
			$this->dispatch('admin_collect_settings_pages');
		}
	}
	
	/**
	 * Listener for ROUTER_COLLECT_ROUTES.  Parses through the list of admin pages and creates a new
	 * route for each and sends to the Router.  Also sets up the default index route and the save_settings
	 * handler.  
	 *
	 * @param   string  The name of the event.
	 * @param	Router	The Router instance.
	 *
	 **/
	public function collectAdminPages($event, $dispatcher) {
		$routes = array();

		foreach($this->_pages as $page) {
			$this->addAdminMenuItem($page->route, $page->title);
			$routes[$page->route] = array(&$this, 'adminPage');
		}
		
		$routes['/admin/save_settings'] = array(&$this, 'saveSettings');
		$routes['/admin'] = array(&$this, 'index');
		$dispatcher->addRoutes($routes);
	}

	/**
	 * Adds an AdminPage instance to the internal pages list for processing.  
	 *
	 * @param   AdminPage  The AdminPage instance to add to the internal pages registry.
	 *
	 **/
	public function addAdminPage(AdminPage $page) {
		$this->_pages[] = $page;
	}	
	
	/**
	 * Adds a menu item to the admin menu for a specific route.  You can use this if you want to use your
	 * own custom settings pages.  
	 *
	 * @param   string  The route of the menu item.
	 * @param	string	The title to display for the menu item.
	 *
	 **/
	public function addAdminMenuItem($route, $title) {
		$this->_menuItems[$route] = $title;
	}
	
	/**
	 * Returns a new AdminPage instance.
	 *
	 * @return AdminPage 
	 *
	 **/
	public function newAdminPage() {
		return new AdminPage();
	}
	
	/**
	 * Listener for the MENUMANAGER_COLLECT_MENUS event. Creates a new Menu instance and save items
	 * based on the internal _menuItems registry.
	 *
	 * @param   string  The name of the event.
	 * @param	MenuManager	The MenuManager instance.
	 *
	 **/
	public function sendMenus($event, $dispatcher) {
		$menu = $dispatcher->newMenu('adminMenu');
		
		foreach($this->_menuItems as $route => $title) {
			$item = $menu->newItem($title, $route);
			$menu->addItem($item);
		}
	}
	
	/**
	 * Handler for all settings pages.  Parses through the internal _pages array to find the matching AdminPage and renders the result.  
	 *
	 * @param   string  The route to handle.
	 * @param	Router	The Router instance.
	 * @param	array	The optional array of args parsed from the route.
	 *
	 **/
	public function adminPage($route, $router, $args, $request) {
		if(!$this->checkUser($request))
			return;
		$this->addEventListenerTo('Templater\\Templater', 'templater_prepare', function($event, $templater) {
			$templater->setTemplateDir(COMPROOT.'AdminInterface/template/');
		});
		
		//Iterate through the pages.
		foreach($this->_pages as $page) {
			//If the route matches, render a form based on the passed settings.
			if($page->route == $route) {
				$this->fh = \FormHelper\FormHelper::instance();
				$this->page = $page;
				$title = $page->title;
				$this->addEventListenerTo('Templater\\Templater', 'templater_prepare', function($event, $templater) use ($title) {
					$templater->setTitle($title);
				});
				$this->render();
			}
		}
	}
	
	/**
	 * Displays a basic landing page for the /admin route.
	 *
	 **/
	public function index($route, $router, $args, $request) {
		if(!$this->checkUser($request))
			return;
		$this->addEventListenerTo('Templater\\Templater', 'templater_prepare', function($event, $templater) {
			$templater->setTemplateDir(COMPROOT.'AdminInterface/template/');
		});
		$this->pages = $this->_pages;
		$this->_setPhpVersion();
		$this->_checkPerms();
		$this->render();
	}
	
	/**
	 * Gets the PHP version to display on the admin index page.
	 *
	 **/
	protected function _setPhpVersion() {
		if (!defined('PHP_VERSION_ID')) {
		    $version = explode('.', PHP_VERSION);
		    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
		}
	}
	
	/**
	 * Checks the permissions for the key directories.
	 *
	 **/
	protected function _checkPerms() {
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
	}
	
	/**
	 * Generic handler for the settings submission.  Saves the results by taking each incoming form field and 
	 * setting the config value of the same name in the component.
	 *
	 * @param   string  The current request route.
	 * @param	Router	The Router instance.
	 * @param	array	The array of POST form values.
	 *
	 **/
	public function saveSettings($route, $dispatcher, $args, $request) 
	{
		if (!$this->checkUser($request))
		{
			return;
		}

		foreach ($this->_pages as $page) 
		{
			if ($page->route == $args['setting-page'])
			{
				$component = $page->component;
				foreach ($page->fields as $setting) 
				{
					$vals = $args[$setting['name']];
					$component->config->setConfigValue($setting['name'], $vals);
					$callback = $page->callback;
					if (is_callable(array($component, $callback)))
					{
						$component->$callback($vals);
					}
				}
				$this->redirectTo($page->route);
				return;
			}
		}
	}
	
	protected function checkUser($request) {
    // if session & user modules don't exist, we'll assume this admininterface
    // isn't auth controlled
    //if (class_exists('Session') && class_exists('UserManager')) {
     // $session = Session::getInstance();
      //if(!$session->getValue('user', false)) {
      //  $this->redirectTo($this->completeUrl('/user/login?ref='.$request->path));
       // return false;
     // }
    //}
		return true;
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
      'dependencies' => array(),
    );
  } // end function getInfo
}
