<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\InvalidAuthorizationCodeException;

class GoogleProvider extends Provider
{
	protected $authorizeUrl = "https://accounts.google.com/o/oauth2/auth";
	protected $accessTokenUrl = "https://accounts.google.com/o/oauth2/token";
	protected $userDataUrl = "https://www.googleapis.com/userinfo/v2/me";


	protected $headers = array(
		'authorize' => array(),
		'access_token' => array(
			'Content-Type' => 'application/x-www-form-urlencoded'
		),
		'user_details' => array(),
	);

	protected function compileScopes()
	{
		return implode(' ', $this->scope);
	}

	protected function getAuthorizeUrl()
	{
		return $this->authorizeUrl;
	}

	protected function getAccessTokenBaseUrl()
	{
		return $this->accessTokenUrl;
	}

	protected function getUserDataUrl()
	{
		return $this->userDataUrl;
	}

	protected function parseTokenResponse($response)
	{
		$data = json_decode($response);
		if (! isset($data->access_token)) {
			throw new InvalidAuthorizationCodeException;
		}
		return $data->access_token;
	}

	protected function parseUserDataResponse($response)
	{
		return json_decode($response, true);
	}

	protected function userId()
	{
		return $this->getProviderUserData('id');
	}

	protected function nickname()
	{
		return $this->getProviderUserData('email');
	}

	protected function firstName()
	{
		return $this->getProviderUserData('given_name');
	}

	protected function lastName()
	{
		return $this->getProviderUserData('family_name');
	}

	protected function imageUrl()
	{
		return $this->getProviderUserData('picture');
	}

	protected function email()
	{
		return $this->getProviderUserData('email');
	}
}