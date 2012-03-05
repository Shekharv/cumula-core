<?php
namespace Authentication;
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
 * CumulaAuth
 *
 * Interface for authentication classes
 *
 * @package		Cumula
 * @subpackage	Core
 * @author     Seabourne Consulting
 */
interface CumulaAuth {
  
  /**
   * The authenticate function of an auth class must take a set of parameters, 
   * usually credentials, and return a response array.
   * @param $params array of auth params
   * @return array response from auth service
   */
	public function authenticate($params);
  
}
