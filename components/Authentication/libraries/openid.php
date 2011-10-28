<?php

class openidAuthentication extends Authentication implements CumulaAuth
{
  public $success = FALSE;
  public $response = array();

  public function __construct() {
    parent::__construct();
  }
  
  
  /**
   * @param $params array This is expected to be an array to comply with the
   * interface, but we only need one element.  Pass array('openid_url' => 'xxx')
   * @return array response from auth service
   */
	public function authenticate($params)
  {
    $this->response['msg'] = '';
    require 'lightopenid/openid.php';
    try 
    {
      $openid = new LightOpenID;
      
      if(!$openid->mode) 
      {
        $openid->identity = $params['openid_url'];
        header('Location: ' . $openid->authUrl());
      } elseif($openid->mode == 'cancel') {
        $this->response['msg'] = 'User has canceled authentication!';
      } else {
        $this->success = $openid->validate();
        $this->response['id'] = $openid->identity;
        $this->response['msg'] = 'User '.$this->response['id']; 
        $this->response['msg'] .= ($this->success) ? ' has ' : 'has not ';
        $this->response['msg'] .= 'logged in.';
      }
    } 
    catch(ErrorException $e) 
    {
      $this->response['msg'] = $e->getMessage();
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