<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

class InstagramProvider extends Provider
{
	protected $authorizeUrl = "https://api.instagram.com/oauth/authorize";
	protected $accessTokenUrl = "https://api.instagram.com/oauth/access_token";
	protected $userDataUrl = "https://api.instagram.com/v1/users/self";
	protected $scope = array(
        'basic',
	);

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

	protected function compileScopes()
	{
		return implode('+', $this->scope);
	}

	protected function parseTokenResponse($response)
	{
		$data = json_decode($response);
		if (! isset($data->access_token)) {
			throw new InvalidAuthorizationCodeException;
		}
		return $data->access_token;
	}

	protected function requestUserData()
	{
		$userData = parent::requestUserData();
		return $userData;
	}

	protected function parseUserDataResponse($response)
	{
		$data = json_decode($response, true);
		return $data['data'];
	}

	protected function userId()
	{
		return $this->getProviderUserData('id');
	}

	protected function nickname()
	{
		return $this->getProviderUserData('username');
	}

	protected function firstName()
	{
		return strstr($this->getProviderUserData('full_name'), ' ', true);
	}

	protected function lastName()
	{
		return substr(strstr($this->getProviderUserData('full_name'), ' '), 1);
	}

	protected function imageUrl()
	{
		return $this->getProviderUserData('profile_picture');
	}

	protected function email()
	{
		return null; // Impossible to get email from Instagram
	}
}
