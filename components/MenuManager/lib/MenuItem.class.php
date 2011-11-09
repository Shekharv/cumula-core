<?php
namespace MenuManager;
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
 * MenuItem Class
 *
 * Describes an individual menu item.
 *
 * @package		Cumula
 * @subpackage	MenuManager
 * @author     Seabourne Consulting
 */
class MenuItem {
	private $_children;
	public $title;
	public $path;
	
	public function __construct($title = null, $path = null) {
		$this->title = $title;
		$this->path = $path;
		$this->_children = array();
	}
	
	public function addChild(MenuItem $item) {
		$this->_children[] = $item;
	}
	
	public function getChildren() {
		return $this->_children;
	}
	
}
