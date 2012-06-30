<?php
namespace Cumula\Application;

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
final class ComponentManager extends \Cumula\Base\Component {
	private $_components = array();
	private $_enabledClasses = array();
	private $_installedClasses = array();
	private $_availableClasses = array();
	private $_startupClasses = array();
	private $componentFiles = array();
	private $_dependencies = array();
	
	private $_installList = array(
		"Cumula\\Components\\Install\\Install", 
		'Cumula\\Components\\FormHelper\\FormHelper', 
//		'Cumula\\Components\\UserManager\\UserManager', 
		'Cumula\\Components\\MenuManager\\MenuManager', 
		'Cumula\\Components\\Authentication\\Authentication',
		'Cumula\\Components\\AdminInterface\\AdminInterface',
		'Cumula\\Components\\CumulaTemplate\\CumulaTemplate',
		'Cumula\\Components\\Devel\\Devel',
	);
	
	private $_loadSuccess;

	/**
	 * Constructor.
	 *
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();

		// Create new events for component management
		$this->addEvent('ComponentInitComplete');
		$this->addEvent('ComponentStartupComplete');

		// Set listeners for events
		$this->bind('ComponentStartupComplete', array($this, 'startup'));

		A('Application')->bind('BootInit', array($this, 'loadComponents'));
		A('Application')->bind('BootStartup', array($this, 'startupComponents'));
		A('Application')->bind('BootShutdown', array($this, 'shutdown'));
		
		// Initialize config and settings
		$this->config = new \Cumula\Application\StandardConfig(CONFIGROOT, 'components.yaml');
		$this->loadSettings();

		// Set output
		$this->_output = array();
		$this->_loadSuccess = false;
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
			'group' => 'Core',
			'dependencies' => array(),
		);
	} // end function getInfo
	
	public function getComponentDependencies($component) {
		if(isset($this->_dependencies[$component]))
			return $this->_dependencies[$component];
		else
			return array();
	}
	
	public function getAllComponentDependencies() {
		return $this->_dependencies;
	}
	
	protected function _generateLabels($type = '_installedClasses') {
		$labels = array();
		if(!isset($this->$type)) 
			return $labels;
			
		foreach($this->$type as $class) {
			if(method_exists($class, 'getInfo')) {
				$info = $class::getInfo();
				$labels[] = $info['name'];
			} else {
				$labels[] = $class;
			}
		}
		return $labels;
	}
	
	/**
	 * Implementation of the basecomponent startup function.
	 * 
	 */
	public function startup()
	{
		A('AdminInterface')->bind('GatherAdminPages', array($this, '_installedAdminConfig'));
		A('AdminInterface')->bind('GatherAdminPages', array($this, '_uninstalledAdminConfig'));
	}
	
	public function _uninstalledAdminConfig() {
		$uninstalled = array_diff($this->_availableClasses, $this->_installedClasses);
		$componentNumber = count($uninstalled) > 0 ? ' <strong>'.count($uninstalled).'</strong>' : '';
		$fields = array();
		$description = 'No uninstalled components.  Go to the <a href="'.A('AdminInterface')->getConfigValue('basePath', '/admin').'/installed-components">Installed Components</a> page to manage current components.';
		if (count($uninstalled) > 0)
		{
			$labels = array();
			foreach($uninstalled as $class) {
				if(method_exists($class, 'getInfo')) {
					$info = $class::getInfo();
					$labels[] = $info['name'];
				} else {
					$labels[] = $class;
				}
			}
			$fields = array('installed_components' => array(
				'title' => 'Uninstalled Components',
				'type' => 'checkboxes',
				'values' => array_merge($uninstalled),
				'labels' => array_merge($labels)
			));
			$description = null;
		}
		return array(
			'New Components' => array(
				'title' => 'New Components'.$componentNumber,
				'description' => $description,
				'parent' => 'Components',
				'config' => $this->config,
				'callback' => array($this, 'installComponents'),
				'fields' => $fields,
			)
		);
	}

	public function _installedAdminConfig() {
		$labels = $this->_generateLabels();
		return array(
			'Installed Components' => array(
				'parent' => 'Components',
				'config' => $this->config,
				'fields' => array(
					'enabled_components' => array(
						'type' => 'checkboxes',
						'title' => 'Enabled Components',
						'values' => $this->_installedClasses,
						'selected' => $this->_enabledClasses,
						'labels' => $labels
					),
				)
			)
		);
	}
	
	public function writeConfig() {
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
		$this->_installedClasses = array_values(array_intersect($this->_availableClasses, (array)$this->config->getConfigValue('installed_components', array())));
		$this->_enabledClasses = array_values(array_intersect($this->_availableClasses, (array)$this->config->getConfigValue('enabled_components', array())));
		$this->_startupClasses = array_values(array_intersect($this->_availableClasses, (array)$this->config->getConfigValue('startup_components', array())));
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
		// If no components are installed, install basic list of components
		if (empty($this->_installedClasses)) 
		{
			$this->installComponents($this->_installList);
		}

		$this->dispatch('ComponentInitComplete');
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
		$this->dispatch('ComponentStartupComplete');
		$this->_loadSuccess = true;
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
				/*if(method_exists($component_class,'getInfo') && ($info = $component_class::getInfo()) && isset($info['dependencies'])) {
					$vals = $info['dependencies'];
					array_walk($vals, function(&$a) {var_dump($a);});
					$this->_dependencies[$component_class] = $vals;
				}*/
				
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
	
	public function componentEnabled($component) {
		return in_array($component, $this->_enabledClasses);
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
			$files = array();
			$component_dirs = array(
				COMPROOT,
				CONTRIBCOMPROOT
				);
			if (COMPDIRS) {
				$component_dirs = array_merge(
					$component_dirs,
					explode("|",COMPDIRS)
					);
			}
			foreach ($component_dirs as $dir) {
				$files = array_merge(
					$files,
					$this->recurseCompDirectory($dir)
					);
			}
			$this->componentFiles = $files;
		}
		return $this->componentFiles;
	} // end function getComponentFiles
	
	protected function recurseCompDirectory($root, $source = null) {
		if ($source === null) {
			$source = $root;
		}
		$suffix = '.component';
		$ret = array();
		foreach(glob(sprintf('{%s*/*,%s*/*%s}', $source, $source, $suffix), GLOB_BRACE) as $file)
		{
			if(is_dir($file)) {
				$ret = array_merge($ret, $this->recurseCompDirectory($root, $file));
			} else if(str_replace(basename($file, $suffix), '', basename($file)) == $suffix) {
				$relativeFile = substr($file, strlen($root));
				$componentPath = substr($relativeFile, 0, strlen($relativeFile)-strlen($suffix));
				$componentName = preg_replace("/\//", "\\", $componentPath);
				$ret[$componentName] = $file;
			}
		}
		return $ret;
	}

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
		$instance = A($component);
		$instance->install();

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
			$installed = $this->enableComponent($component);
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
	public function setEnabledComponents($components, $process = true) 
	{
		if($process) {
			$disable_list = array_diff($this->_enabledClasses, $components);
			$enable_list = array_diff($components, $this->_enabledClasses);
			$this->disableComponents($disable_list);
			$this->enableComponents($enable_list);
		} else {
			$this->_enabledClasses = $components;
		}

	}

	public function disableComponent($component) {
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
