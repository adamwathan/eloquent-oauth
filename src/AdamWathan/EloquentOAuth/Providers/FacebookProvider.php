<?php namespace AdamWathan\EloquentOAuth\Providers;

use Facebook;

class FacebookProvider extends Provider
{
	protected $facebook;
	protected $userData;

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

	public function userDetails()
	{
		$details = parent::userDetails();
		$details->addDetails($this->getUserData());
		return $details;
	}

	protected function accessToken()
	{
		$accessToken = $this->facebook->getAccessToken();
		$this->facebook->setExtendedAccessToken();
		return $accessToken;
	}

	protected function userId()
	{
		return $this->facebook->getUser();
	}

	protected function nickname()
	{
		return $this->getUserData('username');
	}

	protected function firstName()
	{
		return $this->getUserData('first_name');
	}

	protected function lastName()
	{
		return $this->getUserData('last_name');
	}

	protected function email()
	{
		return $this->getUserData('email');
	}

	protected function imageUrl()
	{
		$url = 'https://graph.facebook.com/';
		$url .= $this->getUserData('id');
		$url .= '/picture';
		return $url;
	}

	protected function getUserData($key = null)
	{
		if (! isset($this->userData)) {
			$this->userData = $this->facebook->api('/me');
		}
		if (is_null($key)) {
			return $this->userData;
		}
		return isset($this->userData[$key]) ? $this->userData[$key]: null;
	}
}