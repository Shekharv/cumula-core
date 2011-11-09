<?php
namespace Authentication;


class googleAuthentication extends Authentication implements CumulaAuth
{
  public $success = FALSE;
  public $response = array();

  public function __construct() {
    parent::__construct();
  }
  
  
  /**
   * @param $params array of auth params NOTE: this is unused in the Google auth
   *   component but must be here to adhere to the interface.  Just ignore.
   * @return array response from auth service
   */
	public function authenticate($ignored = NULL)
  {
    $this->response['msg'] = 'No response.';
    require 'lightopenid/openid.php';
    try 
    {
      $openid = new \LightOpenID;
      
      if(!$openid->mode) 
      {
        $openid->identity = 'https://www.google.com/accounts/o8/id';
        header('Location: ' . $openid->authUrl());
      } elseif($openid->mode == 'cancel') {
        $this->response['msg'] = 'User has canceled authentication!';
      } else {
        $this->response['msg'] = 'User ' . ($openid->validate() ? $openid->identity . ' has ' : 'has not ') . 'logged in.';
        $this->response['id'] = $openid->identity;
        $this->success = $openid->validate();
      }
    } 
    catch(ErrorException $e) 
    {
      echo $e->getMessage();
    }
    
    return $this->response;
  }
  
  /**
   * Getter for $success
   */
  public function success()
  {
    return $this->success;
  }

  
}
