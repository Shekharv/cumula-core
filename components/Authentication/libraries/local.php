<?php
namespace Authentication;
use Cumula\CumulaAuth as CumulaAuth;
class localAuthentication extends Authentication implements CumulaAuth
{
  public $success = FALSE;
  public $response = array();
  
  public function __construct()
  {
    parent::__construct();
    
    $this->response = array(
      'msg' => 'There was an error processing your login.  Please try again.',
      'id' => 0,
    );
  }
  
  
  public function authenticate($params)
  {
    if (empty($params['class'])) {
      return $this->response;
    }
    
    $class = $params['class'];
    unset($params['class']);
    
    if (empty($params)) {
      $this->response['msg'] = 'One or more credentials missing.';
      return $this->response;
    }
    
    try 
    {
      $user = call_user_func($class.'::findOne', $params);
      
      if ($user) {
        $this->success = TRUE;
        $this->response = array(
          'msg' => 'User authenticated.',
          'id' => $user->id_str,
        );
      }
    } 
    catch(Exception $e) 
    {
      $this->response['msg'] = $e->getMessage();
    }
    
    return $this->response;
  }
  
  
  public function success()
  {
    return $this->success;
  }
  
}
