<?php
namespace Authentication;
use Cumula\SystemConfig as SystemConfig;
use Cumula\Request as Request;
require_once(dirname(__FILE__) .'/yahoo-yos-social-php5/lib/OAuth/OAuth.php');
require_once(dirname(__FILE__) .'/yahoo-yos-social-php5/lib/Yahoo/YahooOAuthApplication.class.php');

/**
 * Yahoo Authentication Class
 * @package Cumula
 * @author Craig Gardner <craig@seabourneconsulting.com>
 **/
class yahooAuthentication extends Authentication implements CumulaAuth 
{
	/**
	 * Yahoo Consumer Key
	 * @var string
	 **/
	private $yahooConsumerKey;

	/**
	 * Yahoo Consumer Secret
	 * @var string
	 **/
	private $yahooConsumerSecret;

	/**
	 * Yahoo Application ID
	 * @var string
	 **/
	private $yahooApplicationId;

	/**
	 * Success Flag
	 * @var boolean
	 **/
	private $success = FALSE;

	/**
	 * Class Constructor
	 * @param void
	 * @return void
	 **/
	public function __construct() 
	{
		parent::__construct();
		$config = SystemConfig::getInstance();
		$this->yahooConsumerKey = $config->getValue('yahoo_consumer_key', FALSE);
		$this->yahooConsumerSecret = $config->getValue('yahoo_consumer_secret', FALSE);
		$this->yahooApplicationId = $config->getValue('yahoo_application_id', FALSE);
		if ($this->yahooConsumerKey == FALSE || $this->yahooConsumerSecret == FALSE || $this->yahooApplicationId == FALSE) {
			throw new Exception('Yahoo Authentication is not configured.');
		}
	} // end function __construct

	/**
	 * Authenticate the user
	 * @param array $params Parameters passed to the auth url
	 * @return void
	 **/
	public function authenticate($params = array()) 
	{
		$yahoo = new \YahooOAuthApplication($this->yahooConsumerKey, $this->yahooConsumerSecret, $this->yahooApplicationId, $_SERVER['HTTP_HOST']);
		if (count($params) == 0) 
		{
			$callbackUrl = sprintf('http%s://%s/%s', ($_SERVER['SERVER_PORT'] == 443 ? 's' : ''), $_SERVER['HTTP_HOST'], Request::getInstance()->path);
			$requestToken = $yahoo->getRequestToken($callbackUrl);
			$_SESSION['yahoo_request_token'] = array(
				'key' => $requestToken->key,
				'secret' => $requestToken->secret,
			);
			header('Location: '. $yahoo->getAuthorizationUrl($requestToken));
		}
		else 
		{
			$requestToken = new \OAuthConsumer($_SESSION['yahoo_request_token']['key'], $_SESSION['yahoo_request_token']['secret']);
			$yahoo->token = $yahoo->getAccessToken($requestToken, $params['oauth_verifier']);
			$this->response = (array) $yahoo->getProfile();
			$this->response['id'] =$this->response['guid'];
			$this->success = TRUE;
		}
	} // end function authenticate

	/**
	 * Determine whether or not authentication was successful
	 * @param void
	 * @return boolean
	 **/
	public function success() 
	{
		return $this->success;
	} // end function success

} // end class yahooAuthentication extends Authentication implements CumulaAuth
