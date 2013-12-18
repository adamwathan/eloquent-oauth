<?php namespace AdamWathan\EloquentOAuth\Providers;

abstract class Provider
{

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

	abstract public function authorizeUrl();
	abstract public function getUserId();
}