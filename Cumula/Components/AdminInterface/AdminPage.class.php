<?php
namespace AdminInterface;
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
 * AdminPage Class
 *
 * A structured class representing an admin settings page.  The fields property contains an array of arrays 
 * (key value pairs), each representing a single setting to be displayed and saved.  Keys are:
 * 
 * - **name**: the name of the setting. This should be what you save it as in your component config.
 * - **title**: the human readable title of the setting, used to display a label next to the field.
 * - **type**: the type of setting.  Currently supported options are 'checkboxes', 'select', or 'string'
 * - **value**: for use with 'string' type only.  The default value to display.
 * - **values**: for use with 'select' or 'checkboxes'.  The values to display.
 * - **selected**: for use with 'select' or 'checkboxes'.  The default value to select/check.
 * - **labels**: for use with 'checkboxes'.  An optional array of labels to display next to each checkbox.
 *
 * @package		Cumula
 * @subpackage	AdminInterface
 * @author     Seabourne Consulting
 */

class AdminPage {
	/**
	* @var	string	The desired route for the admin settings page.
	**/
	public $route;
	
	/**
	* @var	string	The page and menu title for the admin settings page.
	**/
	public $title;
	
	/**
	* @var	array	An array of field arrays.  See above for information on possible values.
	**/
	public $fields;
	
	/**
	* @var	BaseComponent	The BaseComponent implementation that owns the settings.
	**/
	public $component;
	
	/**
	* @var	function	A callback function to be executed AFTER the settings have been saved.
	**/
	public $callback;
	
	/**
	* @var	string	A text description to put at the top of the settings page.
	**/
	public $description;
	
	/**
	* Constructor.
	**/
	public function __construct() {
		$this->description = null;
	}
}
