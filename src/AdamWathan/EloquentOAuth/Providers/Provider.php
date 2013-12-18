<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\ProviderUserDetails as UserDetails;

abstract class Provider
{
	protected $appId;
	protected $appSecret;
	protected $redirectUrl;

	public function __construct($config)
	{
		$this->appId = $config['id'];
		$this->appSecret = $config['secret'];
		$this->redirectUrl = $config['redirect'];
	}

	public function redirectUrl()
	{
		return $this->redirectUrl;
	}

	public function userDetails()
	{
		return new UserDetails(array(
			'accessToken' => $this->accessToken(),
			'userId' => $this->userId(),
			'nickname' => $this->nickname(),
			'firstName' => $this->firstName(),
			'lastName' => $this->lastName(),
			'email' => $this->email(),
			'imageUrl' => $this->imageUrl(),
		));
	}

	abstract public function authorizeUrl();
	abstract protected function accessToken();
	abstract protected function userId();
	abstract protected function nickname();
	abstract protected function firstName();
	abstract protected function lastName();
	abstract protected function email();
	abstract protected function imageUrl();
}