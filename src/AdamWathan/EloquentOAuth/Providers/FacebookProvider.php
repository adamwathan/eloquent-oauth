<?php namespace AdamWathan\EloquentOAuth\Providers;

use Facebook;

class FacebookProvider extends Provider
{
	protected $facebook;

	public function __construct($config)
	{
		parent::__construct($config);
		$this->scope = $config['scope'];
		$config = array(
			'appId' => $this->appId,
			'secret' => $this->appSecret,
			'allowSignedRequest' => false,
			);
		$this->facebook = new Facebook($config);
	}

	public function authorizeUrl()
	{
		$params = array(
			'scope' => $this->scope,
			'redirect_uri' => $this->redirectUrl
			);

		return $this->facebook->getLoginUrl($params);
	}

	public function getAccessToken()
	{
		$accessToken = $this->facebook->getAccessToken();
		$this->facebook->setExtendedAccessToken();
		return $accessToken;
	}

	public function getUserId()
	{
		return $this->facebook->getUser();
	}
}