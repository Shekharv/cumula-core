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
 * MenuMenu Class
 *
 * Describes a menu of items.
 *
 * @package		Cumula
 * @subpackage	MenuManager
 * @author     Seabourne Consulting
 */
class MenuMenu {
	public $menuId;
	protected $_items;
	
	public function __construct($menuId) {
		$this->menuId = $menuId;
	}
	
	public function addItem(MenuItem $item) {
		$this->_items[] = $item;
	}
	
	public function newItem($title, $path) {
		return new MenuItem($title, $path);
	}
	
	public function getItems() {
		return $this->_items;
	}
}
