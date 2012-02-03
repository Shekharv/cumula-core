<?php
namespace Cumula;

use \ReflectionClass as ReflectionClass;

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
 * ComponentManager Class
 *
 * The base class that handles loading components.
 *
 * This class hooks into the two initial phases of the boot process, BOOT_INIT and BOOT_STARTUP.
 * Module startup happens in two corresponding phases, first the files are loaded, then they are instantiated.
 *
 * BOOT_INIT is used to load the required files in the components directory.
 *
 * BOOT_STARTUP is used to actually instantiate the components.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
final class ComponentManager extends BaseComponent {
	private $_components = array();
	private $_enabledClasses = array();
	private $_installedClasses = array();
	private $_availableClasses = array();
	private $_startupClasses = array();
	private $componentFiles = array();
	
	private $_installList = array("Install\\Install", 
		'FormHelper\\FormHelper', 
		'UserManager\\UserManager', 
		'Templater\\Templater', 
		'Logger\\Logger', 
		'MenuManager\\MenuManager', 
		'Authentication\\Authentication',
		'AdminInterface\\AdminInterface'
	);

	/**
	 * Constructor.
	 *
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();

		// Create new events for component management
		$this->addEvent('component_init_complete');
		$this->addEvent('component_startup_complete');

		// Set listeners for events
		$this->addEventListener('component_startup_complete', array(&$this, 'startup'));

		$this->addEventListenerTo('Application', 'boot_init', 'loadComponents');
		$this->addEventListenerTo('Application', 'boot_startup', 'startupComponents');
		$this->addEventListenerTo('Application', 'boot_shutdown', 'shutdown');
		$this->addEventListenerTo('Cumula\\Autoloader', 'event_autoload', 'getComponentFiles');

		// Initialize config and settings
		$this->config = new \StandardConfig\StandardConfig(CONFIGROOT, 'components.yaml');
		$this->loadSettings();

		// Set output
		$this->_output = array();
	}
	
	/**
	 * Implementation of the getInfo method
	 * @param void
	 * @return array
	 **/
	public static function getInfo() 
	{
		return array(
			'name' => 'Component Manager',
			'description' => 'Component to manage other components',
			'version' => '0.1.0',
			'dependencies' => array(),
		);
	} // end function getInfo
	
	/**
	 * Implementation of the basecomponent startup function.
	 * 
	 */
	public function startup()
	{
		$this->addEventListenerTo('AdminInterface', 'admin_collect_settings_pages', 'setupAdminPages');
	}

	/**
	 * Defines and adds the admin pages to the admin interface, exposing the installed/enabled class lists.
	 * 
	 */
	public function setupAdminPages($event, $dispatcher) {
		$uninstalled = array_diff($this->_availableClasses, $this->_installedClasses);
		$page = $dispatcher->newAdminPage();
		$page->title = 'Components';
		$page->description = 'Below are the installed and enabled components in the system.';
		$page->route = '/admin/installed_components';
		$page->component = &$this;
		$page->callback = 'loadSettings';
		$page->fields = array(array('name' => 'enabled_components', 
			'title' => 'Enabled Components',
			'type' => 'checkboxes',
			'values' => $this->_installedClasses,
			'selected' => $this->_enabledClasses,
			'labels' => $this->_installedClasses),
		);
		$dispatcher->addAdminPage($page);
		
		/**
		 * If there are uninstalled components, show a menu item for those with the number of components in the title 
		 */

		$page = $dispatcher->newAdminPage();
		$page->title = 'New Components';

		if(count($uninstalled) > 0) 
		{
			$componentNumber = ' <strong>'.count($uninstalled).'</strong>';
			$page->title .= $componentNumber;
		}

		$page->description = 'Below are the components available for installation.';
		$page->route = '/admin/new_components';				
		$page->component = &$this;
		$page->callback = 'installComponents';

		if (count($uninstalled) > 0)
		{
			$page->fields = array(array('name' => 'installed_components',
				'title' => 'Uninstalled Components',
				'type' => 'checkboxes',
				'values' => array_merge($uninstalled),
				'labels' => array_merge($uninstalled)
				));
		} 
		else 
		{
			$page->fields = array();
		}
		$dispatcher->addAdminPage($page);
	}
	
	/**
	 * Ensures that the installed and enabled components are saved on shutdown.
	 */
	public function shutdown() 
	{
		$this->config->setConfigValue('installed_components', $this->_installedClasses);
		$this->config->setConfigValue('enabled_components', $this->_enabledClasses);
		$this->config->setConfigValue('startup_components', $this->_startupClasses);
	}

	/**
	 * Loads the saved settings, or if the first bootup, the default settings
	 * 
	 */
	public function loadSettings() 
	{
		$this->_availableClasses = $this->_getAvailableComponents();
		$this->_installedClasses = array_intersect($this->_availableClasses, $this->config->getConfigValue('installed_components', array()));
		$this->_enabledClasses = array_intersect($this->_availableClasses, $this->config->getConfigValue('enabled_components', array()));
		$this->_startupClasses = array_intersect($this->_availableClasses, $this->config->getConfigValue('startup_components', array()));
	}

	/**
	 * Helper function to add a component to the startup list.
	 */
	public function registerStartupComponent($obj) 
	{
		$this->_startupClasses[] = get_class($obj);
	}
	
	/**
	 * Starts the defined startup components during the BOOT_INIT boot phase.
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	public function startStartupComponents()
	{
		foreach ($this->_startupClasses as $className)
		{
			$this->startupComponent($className);
		}
	}

	/**
	 * Helper function gathers the available components from the /components directory.
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	protected function _getAvailableComponents()
	{
		return array_keys($this->getComponentFiles());
	}

	/**
	 * Iterates through the component directory and:
	 * 1) loads the component file
	 * 2) creates a record in the internal library array of the class.  This is used to instantiate the
	 *  components later.
	 * @return unknown_type
	 */
	public function loadComponents() 
	{
		// If no components are installed, install all available components
		if (empty($this->_installedClasses)) 
		{
			$this->installComponents($this->_getAvailableComponents());
		}

		$this->dispatch('component_init_complete');
	}

	/**
	 * This function instantiates the components by iterating through the internal library array and creating
	 * new class instances for each entry.
	 *
	 * After all the components have been instantiated, the event COMPONENT_STARTUP_COMPLETE is dispatched.
	 *
	 * @return unknown_type
	 */
	public function startupComponents() 
	{
		if (empty($this->_enabledClasses)) 
		{
			$this->enableComponents($this->_installedClasses);
		}
		$list = $this->_enabledClasses;

		foreach ($list as $class_name) 
		{
			$this->startupComponent($class_name);
		}
		$this->dispatch('component_startup_complete');
	}

	/**
	 * Registers a new component instance in the internal registry.
	 *
	 * @param $component_class
	 * @return unknown_type
	 */
	public function startupComponent($component_class, $enable_override = FALSE) 
	{
		if(!isset($this->_components[$component_class]))
		{
			if ($enable_override || in_array($component_class, $this->_enabledClasses)) 
			{
				$instance = new $component_class();
				$this->_components[$component_class] = $instance;
				$instance->installAssets();
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Given a class name, returns the matching instance.  If no matching instance is found, returns false.
	 *
	 * @param $className	string	 The classname to search for.
	 * @return unknown_type
	 */
	public function getComponentInstance($className) 
	{
		if(isset($this->_components[$className]))
		{
			return $this->_components[$className];
		}
		else
		{
			return FALSE;
		}
	}

	/**
   * Get the Files that should contain components
   * @param void
   * @return array
   **/
	public function getComponentFiles()
	{
		if (is_null($this->componentFiles) || count($this->componentFiles) == 0)
		{
			foreach(glob(sprintf('{%s*/*.component,%s*/*.component}', COMPROOT, CONTRIBCOMPROOT), GLOB_BRACE) as $file)
			{
				$basename = basename($file, '.component');
				$this->componentFiles[sprintf('%s\\%s', $basename, $basename)] = $file;
			}
		}
		return $this->componentFiles;
	} // end function getComponentFiles

	/*
	 * *************************************************************************
	 * *****************    ComponentManager API    ****************************
	 * *************************************************************************
	 */

	/**
	 * Installs a single component, based on the string $component parameter.
	 */
	public function installComponent($component) 
	{
		$component = Autoloader::absoluteClassName($component);

		if (!in_array($component, $this->_availableClasses)) 
		{
			//throw new Exception("Install fail. $component does not exist, please verify file location");
		}

		if (in_array($component, $this->_installedClasses)) 
		{
			return FALSE;
		}

		$this->_installedClasses[] = $component;
		$this->startupComponent($component, TRUE);
		$instance = $this->getComponentInstance($component);

		if ($instance) 
		{
			$instance->install();
		}

		return $component;
	}

	/**
	 * Installs an array of components.
	 */
	public function installComponents($components) 
	{
		$installed_components = array();
		foreach($components as $component) 
		{
			$installed = $this->installComponent($component);
			if ($installed) $installed_components[] = $component;
		}
		return $installed_components;
	}

	/**
	 * Installs all components in input component array and uninstalls any components not found in the input
	 * component list
	 */
	public function setInstalledComponents($components) 
	{
		$uninstall_list = array_diff($this->_installedClasses, $components);
		$install_list = array_diff($components, $this->_installedClasses);
		$this->uninstallComponents($uninstall_list);
		$this->installComponents($install_list);
	}

	/**
	 * @throws Exception
	 * @param  $component
	 * @return component string if successful, false otherwise
	 */
	public function enableComponent($component) 
	{
		$component = Autoloader::absoluteClassName($component);

		if (in_array($component, $this->_enabledClasses)) 
		{
			return FALSE;
		}

		// Install the component if it's not already
		if (!in_array($component, $this->_installedClasses)) 
		{
			$this->installComponent($component);
		}

		$this->startupComponent($component);
		$instance = $this->getComponentInstance($component);
		if ($instance) 
		{
			$instance->enable();
		}

		$this->_enabledClasses[] = $component;

		return $component;
	}

	/**
	 * Setter for enabling components
	 * @return array of components that were enabled
	 */
	public function enableComponents($components) 
	{
		$enabled_components = array();
		foreach ($components as $component)
	 	{
			$enabled = $this->enableComponent($component);
			if ($enabled) $enabled_components[] = $enabled;
		}
		return $enabled_components;
	}

	/**
	 * Takes an array of components and enables components not currently enabled while disabling enabled components
	 * not in the input list
	 */
	public function setEnabledComponents($components) 
	{
		$disable_list = array_diff($this->_enabledClasses, $components);
		$enable_list = array_diff($components, $this->_enabledClasses);
		$this->disableComponents($disable_list);
		$this->enableComponents($enable_list);
	}

	public function disableComponent($component) {
		$component = Autoloader::absoluteClassName($component);

		$instance = $this->getComponentInstance($component);
		if ($instance) {
			$instance->disable();
			$key = array_search($component, $this->_enabledClasses);
			unset($this->_enabledClasses[$key]);
			return $component;
		} else {
			return FALSE;
		}
	}

	public function disableComponents($components) {
		foreach($components as $component) {
			$this->disableComponent($component);
		}
	}

	public function uninstallComponent($component) {
		$component = Autoloader::absoluteClassName($component);

		if (!in_array($component, $this->_installedClasses)) {
			return FALSE;
		}

		$instance = $this->getComponentInstance($component);
		if ($instance) {
			if (in_array($component, $this->_enabledClasses)) {
				$this->disableComponent($component);
			}
			$instance->uninstall();
			$key = array_search($component, $this->_installedClasses);
			unset($this->_installedClasses[$key]);
			return $component;
		} else {
			return FALSE;
		}
	}

	public function uninstallComponents($components) {
		foreach($components as $component) {
			$this->uninstallComponent($component);
		}
	}


	/**
	 * Getter for enabled components list
	 * @return array of enabled components
	 */
	public function getEnabledComponents() {
		return $this->_enabledClasses;
	}

	/**
	 * Getter for installed components list
	 * @return array of installed components
	 */
	public function getInstalledComponents() {
		return $this->_installedClasses;
	}

	/**
	 * Getter for startup components
	 * @return array of startup components
	 */
	public function getStartupComponents() {
		return $this->_startupClasses;
	}

	/**
	 * Getter for available components
	 * @return array of available components
	 */
	public function getAvailableComponents() {
		return $this->_availableClasses;
	}

}
