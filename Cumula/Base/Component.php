<?php
namespace Cumula\Base;
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
abstract class Component extends \Cumula\Application\EventDispatcher {
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
		$this->config = $this->constructConfig();
		
		A('ComponentManager')->bind('ComponentStartupComplete', array($this, 'startup'));
		A('Application')->bind('BootShutdown', array($this, 'shutdown'));
		$this->addEvent('RenderFile');
		$this->installAssets();
	}
	
	public function getConfigValue($name, $default) {
		return $this->config->getConfigValue($name, $default);
	}
	
	public function setConfigValue($name, $value) {
		$this->config->setConfigValue($name, $value);
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

	/**********************************************
	* Miscellaneous Installation Functions
	***********************************************/

	/**
	 * Load a config based on this class
	 **/
	public function constructConfig() {
		return new \Cumula\Application\StandardConfig(CONFIGROOT, get_class($this).'.yaml');
	}
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

		$files = glob(sprintf('{%s/assets,%s/assets}', $this->rootDirectory(), $this->rootDirectory()), GLOB_BRACE | GLOB_NOSORT);
		if (is_array($files) && count($files) > 0)
		{
			$assetDir = implode(DIRECTORY_SEPARATOR, array(APPROOT, 'public', 'assets'));
			if (is_dir($assetDir) === FALSE) {
				mkdir($assetDir);
			} else {
				if($sc = \A('SystemConfig')){
					if($sc->getValue('setting_environment', false) != 'development')
						return;
				}
			}

			$componentPublicAssetDir = $assetDir . DIRECTORY_SEPARATOR . $class;
			if (is_dir($componentPublicAssetDir) === FALSE) {
				mkdir($componentPublicAssetDir);
			}
			foreach ($files as $componentAssetDir) {
				\copyDir($componentAssetDir, $componentPublicAssetDir);
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
	public function render($arg1 = array(), $arg2 = array()) {
		$blockName = 'content';
		if(is_array($arg1)) {
			$bt = debug_backtrace(false); //TODO: See if there's a better way to do this than debug backtrace.
			$caller = $bt[1]['function'];
			$fileName = dirname($this->_getThisFile()).'/views/'.$caller.'.tpl.php';	
			$args = $arg1;	
		} else if (is_string($arg1)) {
			$blockName = false;
			$args = $arg2;
			if(!file_exists($arg1))
				$fileName = $this->rootDirectory().DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$arg1;
			if(!file_exists($fileName)) {
				$fileName = $arg1;
			}
		}
		return $this->renderBlock($this->renderDefault($fileName, $args), $blockName);
	}
	
	public function __call($name, $args) {
		if(strstr($name,'render')) {
			global $cm;
			$cm = $this;
			return call_user_func_array(array(A('Renderer'), $name), $args);
		}
	}
	
	/*protected function renderPlain($output, $contentType = 'text/plain', $useTemplate = false) {
		A('Response')->response['content'] = $output;
		A('Response')->response['headers']['Content-Type'] = $contentType;
		if(!$useTemplate) 
			A('Application')->unbind('BootPostprocess', array(A('Templater'), 'postProcessRender'));

	}*/
	
	/**
	 * Returns a rendered view specified in $file_name.  $args is exposed to the view.
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	/*public function renderPartial($file_name = null, $args = array()) {
		$ext = '.tpl.php';
		if(pathinfo($file_name, PATHINFO_EXTENSION) == '' && !strpos($file_name, $ext)) {
			$file_name = dirname($this->_getThisFile()).'/views/'.$file_name.$ext;
		}
		$this->dispatch('RenderFile', array($file_name), function($new_filename) use (&$file_name) {
			if($new_filename && $new_filename != '')
				$file_name = $new_filename;
		});
		extract($args, EXTR_OVERWRITE);
		ob_start();
		include $file_name;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}*/
	
	/*public function renderNothing() {
		A('Response')->response['content'] = '';
		A('Application')->unbind('BootPostprocess', array(Templater::instance(), 'postProcessRender'));
	}*/
	
	
	/*public function render404() {
		A('Response')->send404();
		if(!$useTemplate) 
			A('Application')->unbind('BootPostprocess', array(\A('Templater'), 'postProcessRender'));
	}*/
	
	/*public function renderCLA($output) {
		A('Response')->response['content'] = $output;
	}*/
	
	/**
	 * Adds a block to the render queue for dispatching to the templater.
	 * 
	 */
	/*public function renderContent($content, $var_name = 'content') {
		$block = new \ContentBlock\ContentBlock();
		$block->content = $content;
		$block->data['variable_name'] = $var_name;
		$this->addOutputBlock($block);
	}*/
	
	/*protected function renderString($markup, $args = array()) {
		extract($args, EXTR_OVERWRITE);
		ob_start();
		eval("?>$markup<?");
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}*/
	
	/**
	 * @param $event
	 * @param $args
	 * @return unknown_type
	 */
	/*public function sendOutput($event, $args) {
		foreach($this->_output as $block) {
			$args[$block->data['variable_name']] = $block;
		}
	}*/

	/**
	 * Adds an output block to the templater
	 * 
	 * @param $block
	 * @return unknown_type
	 */
	/*public function addOutputBlock($block) {		
		if(empty(A('Response')->response['data'][$block->data['variable_name']]))
		{
			A('Response')->response['data'][$block->data['variable_name']] = array($block);
		}
		else 
		{
			A('Response')->response['data'][$block->data['variable_name']][] = $block;
		}
	}*/
	

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
		A('Response')->send302($this->completeUrl($url));
	}
	
	/**
	 * returns a url that includes the system base path
	 * 
	 * @param $url
	 * @return unknown_type
	 */
	public function completeUrl($url) {
		$base = A('SystemConfig')->getValue(SETTING_DEFAULT_BASE_PATH);
		return ($base == '/') ? $url : $base.$url;
	}
	
	/**
	 * Returns the system-wide default datastore setting
	 * 
	 * @return unknown_type
	 */
	public function defaultDataStore() {
		$store = A('SystemConfig')->getValue('default_datastore', 'Cumula\\DataStore\\YAML');
		return $store;
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

}
