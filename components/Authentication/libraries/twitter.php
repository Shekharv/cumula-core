<?php
namespace Authentication;

class twitterAuthentication extends Authentication implements CumulaAuth
{
  public $success = FALSE;
  public $response = array();
  
  private $consumer_key;
  private $consumer_secret;
  private $oauth_token;
  private $oauth_token_secret;
  
  private $twitterObj;
  private $twitterObjUnAuth;

  public function __construct() {
    parent::__construct();
    
    include 'jmathai-twitter-async/EpiCurl.php';
    include 'jmathai-twitter-async/EpiOAuth.php';
    include 'jmathai-twitter-async/EpiTwitter.php';
    
    // These are Jason Socha's keys for the MyFCC app registered under username
    // code_it_fast.  This should be abstracted out to settings on an admin page.
    $this->consumer_key = 'v7S3IclbgXvycTyIhBfCw';
    $this->consumer_secret = 'Ml8a9YVdzxrOkagYuCQKiIbzlSzlj1mrvJA2lUKT9Sg';
    $this->oauth_token = '152496090-f9DLgcruFoJtGFXEJG8v0ukbIZM8voPnaBpaitXv';
    $this->oauth_token_secret = 'pPVTMx96ZlqWjKPLxW6B8OKXcWbA51RrVkSlJXqFnk';
    
    //$this->twitterObj = new EpiTwitter($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);
    $this->twitterObjUnAuth = new \EpiTwitter($this->consumer_key, $this->consumer_secret);
    
    //$this->twitterObj->useAsynchronous(FALSE);
    $this->twitterObjUnAuth->useAsynchronous(FALSE);
  }
  
  
  /**
   * @param $params array of auth params NOTE: this is unused in the Twitter auth
   *   component but must be here to adhere to the interface.  Just ignore.
   * @return array response from auth service
   */
	public function authenticate($ignored = NULL)
  {
    $this->response['msg'] = 'No response.';
    try 
    {
      if (!empty($_GET['denied'])) {
        //user has clicked NO or Cancel
        $this->redirectTo('/');
      }
      elseif (empty($_GET['oauth_token']))
      {
        $url = $this->twitterObjUnAuth->getAuthenticateUrl(NULL, array('oauth_callback' => 'http://'.$_SERVER["HTTP_HOST"].'/auth_twitter'));
        //$url = $this->twitterObj->getAuthenticateUrl(NULL, array('oauth_callback' => 'http://'.$_SERVER["HTTP_HOST"].'/auth_twitter'));
        //print_r($url);
        header('Location:'.$url);
      }
      else 
      {
//        $this->twitterObj->setToken($_GET['oauth_token']);  
//        $token = $this->twitterObj->getAccessToken();  
//        $this->twitterObj->setToken($token->oauth_token, $token->oauth_token_secret); 
//        
        $this->twitterObjUnAuth->setToken($_GET['oauth_token']);  
        $token = $this->twitterObjUnAuth->getAccessToken();  
        $this->twitterObjUnAuth->setToken($token->oauth_token, $token->oauth_token_secret);
        //print_r($this->twitterObj);
        //$response = $this->twitterObj->get('/account/verify_credentials.json');
        //$this->response = $response->response;
        
        $response = $this->twitterObjUnAuth->get('/account/verify_credentials.json');
        $this->response = $response->response;
        $this->response['id'] = 'http://twitter.com/'.$this->response['screen_name'];
        $this->success = TRUE;
      }
    } 
    catch(Exception $e) 
    {
      print_r($e->getMessage());
      print_r($e->getCode());
      print_r($e->getTraceAsString());
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
