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
 * CumulaConfig Interface
 *
 * The interface that defines a common Cumula Configuration data store.
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
interface CumulaConfig {
	public function getConfigValue($config);
	public function setConfigValue($config, $value);
	public function deleteConfigValue($config);
	
	public function toXml();
	public function toYaml();
	public function toArray();
	public function toString();
	
	public function serialize();
	public function unserialize();
}