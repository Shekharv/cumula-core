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
 * CumulaTemplater Interface
 *
 * Interface for Templater classes.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
interface CumulaTemplater {
	
	public function __construct();
	
	public function setTemplateDir($dir);
	
}