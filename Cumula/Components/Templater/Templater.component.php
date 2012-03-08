<?php
namespace Cumula\Components\Templater;
use \Cumula\Component\BaseComponent as BaseComponent;
use \Cumula\Templater\TemplaterInterface as CumulaTemplater;
use \Cumula\Application as Application;
use \Cumula\Config\System as SystemConfig;

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
 * Templater Component
 *
 * The default templating system.  Provides an interface for specifying a specific php template to render, and
 * exposes the content blocks as variables.
 *
 * @package		Cumula
 * @subpackage	Templater
 * @author     Seabourne Consulting
 */
class Templater extends BaseComponent implements CumulaTemplater {
	
	protected $_template_dir;
	protected $_template_file;
	protected $_output;
	protected $_title;
	
	public function __construct() {
		parent::__construct();
	

		if(!A('Request')->cli) {
			A('Application')->bind('BootPostprocess', array($this, 'postProcessRender'));
			$this->bind('TemplaterRender', array($this, 'renderTemplate'));
		}

		$this->_template_dir = $this->config->getConfigValue('template_directory', ROOT.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'templates');
		$this->_output = '';
		$this->_title = SystemConfig::instance()->getValue(SETTING_SITE_TITLE);
	}
	
	public function setOutput($output) {
		$this->_output .= (string)$output;
	}
	
	public function getOutput() {
		return $this->_output;
	}
	
	public function setTemplateDir($dir) {
		$this->_template_dir = $dir;
	}
	
	public function setTemplateFile($file) {
		$this->_template_file = $file;
	}
	
	public function getTemplateDir() {
		return $this->_template_dir;
	}
	
	public function getTemplateFile() {
		if (is_null($this->_template_file))
		{
			$templateFile = $this->config->getConfigValue('template_file', 'template.tpl.php');
			$this->setTemplateFile($templateFile);
		}
		return $this->_template_file;
	}
	
	public function postProcessRender($event, $dispatcher, $request, $response) {
		//$args = $response->data;
		$args = array('stylesheets' => array(), 
					  'javascript' => array(), 
					  'meta' => array(), 
					  'title' => '', 
					  'content' => '');
		
		$this->dispatch('templater_prepare', array($args));
		$args = $response->response['data'];
		$this->dispatch('TemplaterRender', array($args, $request, $response));
		
		$response->response['content'] = $this->_output;
		
		$this->dispatch('TemplaterCleanup', array($args));
	}
	
	protected function _processStylesheets($stylesheets = array()) {
		$output = '';
		foreach($stylesheets as $stylesheet) {
			$output .= '<link rel="stylesheet" type="text/css" href="'.$stylesheet.'">\n';
		}
		return $output;
	}
	
	protected function _processJavascripts($javascripts = array()) {
		$output = '';
		foreach($javascripts as $javascript) {
			$output .= '<script type="text/javascript" src="'.$javascript.'"></script>\n';
		}
		return $output;
	}
	
	protected function _processMeta($meta = array()) {
		$output = '';
		foreach($meta as $name => $content) {
			$output .= '<meta name="'.$name.'" content="'.$content.'">\n';
		}
		return $output;
	}
	
	public function renderTemplate($event, $dispatcher, $args) {
		$args = $this->_processArgs($args);
		$fileName = $this->_template_dir.DIRECTORY_SEPARATOR.$this->getTemplateFile();
		$args['title'] = $this->_title;
		$this->setOutput(\Cumula\Renderer::renderFile($fileName, $args));
	}
	
	protected function _processArgs($args) {
		$output = array();
		if(!array_key_exists('content', $args))
			return array();
		foreach($args as $region) {
			foreach($region as $block) {
				$output[$block->data['variable_name']] = $block->content;
			}
		}
		return $output;
	}
	
	public function setTitle($title) {
		$this->_title = $title;
	}

  /**
   * Implementation of the getInfo method
   * @param void
   * @return array
   **/
  public static function getInfo() {
    return array(
      'name' => 'Cumula Templater',
      'description' => 'Default Template Class',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}
