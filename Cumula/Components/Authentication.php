<?php
namespace Cumula\Components;
use \Cumula\Component\BaseComponent as BaseComponent;

require 'Authentication/AuthInterface.php';

class Authentication extends BaseComponent
{
  public function __construct() {
    parent::__construct();
  }
  
  public function factory($service)
  {
    $library_path = 'Authentication/lib/'.$service.'.php';
    if (!file_exists($library_path)) {
      throw new Exception('Auth library not found');
      return FALSE;
    }
    
    require_once($library_path);
		$class = sprintf('Cumula\\Components\\%sAuthentication', $service);
    return new $class();
    
  }
  

  /**
   * Implementation of the getInfo method
   * @param void
   * @return array
   **/
  public static function getInfo() {
    return array(
      'name' => 'Authentication Comiponent',
      'description' => 'Handle Authentication within Cumula',
      'version' => '0.1.0',
      'dependencies' => array(),
    );
  } // end function getInfo
}
  
