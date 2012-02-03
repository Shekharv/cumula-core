<?php
namespace Authentication;
use Cumula\SystemConfig as SystemConfig;
require_once(dirname(__FILE__) .'/facebook-php-sdk/src/facebook.php');

class facebookAuthentication extends Authentication implements CumulaAuth
{
	/**
	 * Response array
	 * @var array
	 **/
	public $response = array();

	/**
	 * Success Flag
	 * @var booleane
	 **/
  protected $success = FALSE;

	/**
	 * Facebook OAuth Token
	 * @var string
	 **/
	private $oauthToken;
  
	/**
	 * Class Constructor
	 * @param void
	 * @return void
	 **/
	public function __construct() 
	{
		parent::__construct();

		$config = SystemConfig::getInstance();
		$this->fbClientId = $config->getValue('facebook_client_id', FALSE);
		$this->fbClientSecret = $config->getValue('facebook_client_secret', FALSE);
		if ($this->fbClientId == FALSE || $this->fbClientSecret == FALSE) 
		{
			throw new Exception('Facebook Authentication is not configured');
		}
		$this->redirectUri = sprintf('http://%s/auth_facebook', $_SERVER['HTTP_HOST']);
	} // end function __construct

  /**
   * @param $params array of auth params
   * @return array response from auth service
   */
	public function authenticate($params)
  {
		$facebook = new \Facebook(array(
			'appId' => $this->fbClientId,
			'secret' => $this->fbClientSecret,
		));
		if (count($params) == 0) 
		{
			header('Location: '. $facebook->getLoginUrl());
		}
		else
	 	{
			$this->response = $facebook->api('/me');
			$this->success = isset($this->response['id']);
		}
  }
  
  /**
   * Getter for $success
   */
  public function success()
  {
    return $this->success;
  }

  
}
