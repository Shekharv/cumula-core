<?php
namespace Cumula;
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
 * BaseComponent Class
 *
 * The abstract BaseComponent class is the basis of all Cumula components.
 *
 * ### Events
 * The BaseComponent Class defines the following events:
 *
 * #### EVENT_LOGGED
 * This event is fired whenever the base component logging functions are called.
 *
 * **Args**:
 * 
 * 1. **LogLevel**: the loglevel of the message
 * 2. **Message**: the text of the log message
 * 3. **Args**: an optional array of args to be logged
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
abstract class BaseComponent extends EventDispatcher {
	protected $rootDirectory;
	public $config;
	protected $_output;
	protected $_dataStore;
	
	/**
	 * Constructor.
	 * 
	 * @return unknown_type
	 */
	public function __construct() {
		parent::__construct();
		$this->_output = array();
		$this->config = new \StandardConfig\StandardConfig(CONFIGROOT, get_class($this).'.yaml');
		
		$this->addEventListenerTo('ComponentManager', 'component_startup_complete', 'startup');
		$this->addEventListenerTo('Application', 'boot_shutdown', 'shutdown');
	}
	

	/**********************************************
	* Component Callback Functions
	***********************************************/
	/**
	 * Run once when the module is first installed.
	 * 
	 * Placeholder function.  Should be overridden in client implementations to do anything.
	 * 
	 * @return unknown_type
	 */
	public function install() {
		
	}
	
	/**
	 * Run once when the module is uninstalled. TODO: Implement in ComponentManager
	 * 
	 * Placeholder function.  Should be overridden in client implementations to do anything.
	 * 
	 * @return unknown_type
	 */
	public function uninstall() {
		
	}
	
	/**
	 * Run when the module is enabled.
	 * 
	 * Placeholder function.  Should be overridden in client implementations to do anything.
	 * 
	 * @return unknown_type
	 */
	public function enable() {
		
	}
	
	/**
	 * Run when the module is disabled.
	 * 
	 * Placeholder function.  Should be overridden in client implementations to do anything.
	 * 
	 * @return unknown_type
	 */
	public function disable() {
		
	}
	
	/**
	 * Placeholder function.  Should be overridden in client implementations to do anything.
	 * 
	 * @return unknown_type
	 */
	public function startup() {
		
	}
	
	/**
	 * Placeholder function.  Should be overridden in client implementations to do anything.
	 * 
	 * @return unknown_type
	 */
	public function shutdown() {
		
	}
	

	/**
	 * Static method to provide information to the system aobut themselves
	 * @param void
	 * @return array
	 **/
	public static function getInfo() 
	{
		$class = get_class($this);
		throw new \Exception(sprintf('%s needs  to implement getInfo itself', $class));
	} // end function getInfo

	/**********************************************
	* Miscellaneous Installation Functions
	***********************************************/
	/**
	 * Install the assets for the module in the public directory
	 * @param void
	 * @return void
	 **/
	public function installAssets() {
		$class = get_class($this);
		if (stripos($class, '\\'))
		{
			$classExploded = explode('\\', $class);
			$class = $classExploded[1];
		}

		$files = glob(sprintf('{%s/%s/assets,%s/%s/assets}', COMPROOT, $class, CONTRIBCOMPROOT, $class), GLOB_BRACE | GLOB_NOSORT);
		if (is_array($files) && count($files) > 0)
		{
			$assetDir = implode(DIRECTORY_SEPARATOR, array(APPROOT, 'public', 'assets'));
			if (is_dir($assetDir) === FALSE) {
				mkdir($assetDir);
			} else {
				if($sc = \I('SystemConfig')){
					if($sc->getValue('setting_environment', false) != 'development')
						return;
				}
			}

			$componentPublicAssetDir = $assetDir . DIRECTORY_SEPARATOR . $class;
			if (is_dir($componentPublicAssetDir) === FALSE) {
				mkdir($componentPublicAssetDir);
			}
			foreach ($files as $componentAssetDir) {
				$this->copyAssetFiles($componentAssetDir, $componentPublicAssetDir);
			}
		}
	} // end function installAssets

	/**********************************************
	* Rendering Functions
	***********************************************/
	/**
	 * Renders a specific filename, or a view with the filename matching the original function.  The 
	 * rendered content is sent to the templater as a block using the $var_name param.
	 * 
	 */
	public function render($file_name = null, $var_name = 'content') {
		if($file_name == null) {
			$bt = debug_backtrace(false); //TODO: See if there's a better way to do this than debug backtrace.
			$caller = $bt[1]['function'];
			$file_name = dirname($this->_getThisFile()).'/views/'.$caller.'.tpl.php';
		}
		$contents = $this->renderPartial($file_name);
		$this->renderContent($contents, $var_name);
	}
	
	/**
	 * Returns a rendered view specified in $file_name.  $args is exposed to the view.
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	public function renderPartial($file_name = null, $args = array()) {
		$ext = '.tpl.php';
		if(pathinfo($file_name, PATHINFO_EXTENSION) == '' && !strpos($file_name, $ext)) {
			$file_name = dirname($this->_getThisFile()).'/views/'.$file_name.$ext;
		}
		extract($args, EXTR_OVERWRITE);
		ob_start();
		include $file_name;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/**
	 * Adds a block to the render queue for dispatching to the templater.
	 * 
	 */
	public function renderContent($content, $var_name = 'content') {
		$block = new \ContentBlock\ContentBlock();
		$block->content = $content;
		$block->data['variable_name'] = $var_name;
		$this->addOutputBlock($block);
	}
	
	/**
	 * @param $event
	 * @param $args
	 * @return unknown_type
	 */
	public function sendOutput($event, $args) {
		foreach($this->_output as $block) {
			$args[$block->data['variable_name']] = $block;
		}
	}

	/**
	 * Adds an output block to the templater
	 * 
	 * @param $block
	 * @return unknown_type
	 */
	public function addOutputBlock($block) {
		$response = Response::instance();
		
		if(empty($response->response['data'][$block->data['variable_name']]))
		{
			$response->response['data'][$block->data['variable_name']] = array($block);
		}
		else 
		{
			$response->response['data'][$block->data['variable_name']][] = $block;
		}
	}
	

	/**********************************************
	* Utility Functions
	***********************************************/
	/**
	 * Convenience function to return the LSB instance.
	 * 
	 * @return unknown_type
	 */
	protected function _getThis() {
		return $this;
	}
	
	/**
	 * Returns the filepath of the basecomponent instance.
	 * 
	 */
	protected function _getThisFile() {
		$ref = new \ReflectionClass(static::_getThis());
		return $ref->getFileName();
	}
	
	/**
	 * Redirects the client to the provided url.
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	public function redirectTo($url) {
		Response::instance()->send302($this->completeUrl($url));
	}
	
	/**
	 * returns a url that includes the system base path
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	public function completeUrl($url) {
		$base = SystemConfig::instance()->getValue(SETTING_DEFAULT_BASE_PATH);
		return ($base == '/') ? $url : $base.$url;
	}
	
	/**
	 * Returns the system-wide default datastore setting
	 * 
	 * @return unknown_type
	 */
	public function defaultDataStore() {
		return SystemConfig::instance()->getValue('default_datastore', 'YAMLDataStore\\YAMLDataStore');
	}
	
	public function linkTo($title, $url, $args = array()) {
		$output = '<a href="'.$this->completeUrl($url).'" ';
		foreach($args as $key => $value) {
			$output .= $key.'="'.$value.'" ';
		}
		$output .= ">$title</a>";
		return $output;
	}
	
	/**
	 * Returns the root directory for the component.
	 * 
	 * @return unknown_type
	 */
	public function rootDirectory() {
		$class = new \ReflectionClass(get_class($this));
		return dirname($class->getFileName());	
	}
	/**
	 * Recursive function to re-create the filestructure in the
	 * component's asset directory in the public asset directory
	 * @param string $source
	 * @param string $destination
	 * @return void
	 **/
	private function copyAssetFiles($source, $destination) {
		if (is_dir($source)) {
			// Find all of the files in the directory and create directories
			// for the subdirectories
			foreach(glob($source .'/*', GLOB_NOSORT) as $file) {
				$dirname = basename($file);
				$newDestination = $destination . DIRECTORY_SEPARATOR . $dirname;
				if (is_dir($file) && is_dir($newDestination) === FALSE) {
					mkdir($newDestination, 0777, TRUE);
				}
				$this->copyAssetFiles($file, $newDestination);
			}
		}
		else {
			// Copy the file to the public assets directory
			copy($source, $destination);
		}
	} // end function copyAssetFiles
}
