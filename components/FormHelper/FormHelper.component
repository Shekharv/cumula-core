<?php
namespace FormHelper;

use Cumula\BaseComponent as BaseComponent;
use Cumula\SimpleSchema as SimpleSchema;

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
 * FormHelper Component
 *
 * Provides an API for creating forms, protecting against man-in-the-middle attacks, and sanitizing form
 * values.
 *
 * @package		Cumula
 * @subpackage	FormHelper
 * @author     Seabourne Consulting
 */
class FormHelper extends BaseComponent {
	private static $_formId;
	
	public function __construct() {
		parent::__construct();
		$ds = $this->defaultDataStore();
		$schema = new SimpleSchema(array('id' => 'string',
									 'value' => 'string'), 
								   'id', 
								   'config');
		$this->_dataStore = new $ds($schema, array('source_directory' => DATAROOT, 'filename' => 'data.yaml'));
		self::$_formId = '';
	}
	
	public function startup() {
		$this->_dataStore->connect();
		//Is a form submission
		$this->addEventListenerTo('Application', 'boot_preprocess', 'formCheck');
	}
	
	public function formCheck($event, $dispatcher, $request, $response) {
		if(array_key_exists('validate_id', $request->params)) {
			$this->_logInfo("params is ", $request->params);
			$id = $request->params['validate_id'];
			$key = $request->params['validate_value'];
			//Check id:key is valid and matches saved version
			$check_key = $this->_dataStore->query(array('id' => $id));
			//If key is not valid, return 
			if($key != $check_key) {
				$response->send405();
			} else {
				$this->_dataStore->destroy($key);
			}
		}
	}
	
	public function formTag($action, $id, $method = 'POST', $options = array()) {
		self::$_formId = $id;
		$output = "<form action=\"".$this->completeUrl($action)."\" method=\"$method\" id=\"$id\">";
		if(!array_key_exists('validate', $options) || $options['validate'] == true) {
			$idHash = md5(session_id().$action);
			$valHash = md5((string)microtime(true).(string)rand(0, 1000));
			$obj = $this->_dataStore->newObj();
			$obj->id = $idHash;
			$obj->value = $valHash;
			$this->_dataStore->create($obj);
			$output .= '<input type="hidden" name="validate_id" value="'.$idHash.'">';
			$output .= '<input type="hidden" name="validate_value" value="'.$valHash.'">';
		}
		return $output;
	}
	
	public function formEnd() {
		return '</form>';
	}
	
	public function labelFor($label, $for, $attrs = array()) {
		$attributes = '';
		foreach($attrs as $key => $val) {
			$attributes .= $key.'="'.$val.'" ';
		}

		$for = (self::$_formId ? self::$_formId .'-' : '') . strtolower($for);
		return sprintf('<label for="%s" %s>%s</label>', $for, $attributes, $label);
	}
	
	public function textFieldTag($name, $value = null, $options = array()) {
		return $this->_buildTag('text', $name, $value, $options);
	}
	
	public function textAreaTag($name, $value = null, $options = array()) {
		$output = "<textarea name=\"$name\" ";
		foreach($options as $key => $val) {
			$output .= " $key=\"$val\" ";
		}
		$output .= ">$value</textarea>";
		return $output;
	}
	
	public function selectTag($name, $values, $selected = null, $options = array()) {
		if(is_array($values))
			$values = $this->arrayToOptions($values, $selected);
			
		$output = "<select name=\"$name\" ";
		foreach($options as $key => $val) {
			$output .= " $key=\"$val\" ";
		}
		$output .= ">$values</select>";
		return $output;
	}
	
	public function arrayToOptions($options, $selected = null) {
		$output = '';
		foreach($options as $key => $value) {
			$s = ($selected == $value ? "selected=\"selected\"" : '');
			$output .= "<option $s value=\"$value\">$key</option>";
		}
		return $output;
	}
	
	public function checkboxTag($name, $value, $checked = false, $options = array()) {
		if($checked)
			$options['checked'] = 'true';
		return $this->_buildTag('checkbox', $name, $value, $options);
	}
	
	public function radioButtonTag($name, $value, $checked = false, $options = array()) {
		$options['checked'] = $checked;
		return $this->_buildTag('radio', $name, $value, $options);
	}
	
	public function fileFieldTag($name, $value = null, $options = array()) {
		return $this->_buildTag('file', $name, $value, $options);
	}
	
	public function passwordFieldTag($name, $value = null, $options = array()) {
		return $this->_buildTag('password', $name, $value, $options);
	}
	
	public function hiddenFieldTag($name, $value = null, $options = array()) {
		return $this->_buildTag('hidden', $name, $value, $options);
	}
	
	public function submitTag($name, $options = array()) {
		return $this->_buildTag('submit', $name, $name, $options);
	}
	
	protected function _buildTag($type, $name, $value, $attrs) {
		if (!isset($attrs['id'])) {
			$id = (self::$_formId ? self::$_formId .'-' : '') . str_ireplace('[]', '', $name);
		}
		else {
			$id = $attrs['id'];
		}
		//if($value)
		//	$id .= "-".strtolower($value);
		$output = "<input id=\"$id\" type=\"$type\" name=\"$name\" value=\"$value\" ";
		foreach($attrs as $key => $val) {
			$output .= " $key=\"$val\" ";
		}
		$output .= ">";
		return $output;
	}
  /**
   * Implementation of the getInfo method
   * @param void
   * @return array
   **/
  public static function getInfo() {
    return array(
      'name' => 'Cumula Form Helper',
      'description' => 'Helper Component for creating, protecting, and sanitizing forms',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}
