<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\InvalidAuthorizationCodeException;

class GitHubProvider extends Provider
{
	protected $authorizeUrl = "https://github.com/login/oauth/authorize";
	protected $accessTokenUrl = "https://github.com/login/oauth/access_token";
	protected $userDataUrl = "https://api.github.com/user";

	protected $headers = array(
		'authorize' => array(),
		'access_token' => array(
			'Accept' => 'application/json'
		),
		'user_details' => array(
			'Accept' => 'application/vnd.github.v3'
		),
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
		$data = json_decode($response, true);
		return $data;
	}

	protected function userId()
	{
		return $this->getProviderUserData('id');
	}

	protected function nickname()
	{
		return $this->getProviderUserData('login');
	}

	protected function firstName()
	{
		return strstr($this->getProviderUserData('name'), ' ', true);
	}

	protected function lastName()
	{
		return substr(strstr($this->getProviderUserData('name'), ' '), 1);
	}

	protected function imageUrl()
	{
		return $this->getProviderUserData('avatar_url');
	}

	protected function email()
	{
		$url = $this->getUserDataUrl() .'/emails';
		$url .= "?access_token=".$this->accessToken;
		$request = $this->httpClient->get($url, array(
			'Accept' => 'application/vnd.github.v3'
			));
		$response = $request->send();
		$emails = $response->json();
		foreach ($emails as $email) {
			if ($email['primary']) {
				return $email['email'];
			}
		}
		return $emails[0]['email'];
	}
}