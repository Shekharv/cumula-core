<?php
namespace ContentBlock;
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
 * ContentBlock Class
 *
 * The content block represents output content displayed by Cumula.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
class ContentBlock extends \Cumula\EventDispatcher {
	public $data;
	public $content = '';
	
	public function __construct() {
		parent::__construct();
		$this->data = array();
	}
	
	public function render() {
		return $this->content;
	}
	
	public function __toString() {
		return $this->render();
	}
}
