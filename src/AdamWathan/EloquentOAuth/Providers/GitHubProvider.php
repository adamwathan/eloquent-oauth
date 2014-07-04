<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

class GitHubProvider extends Provider
{
	protected $baseUrl = "https://github.com";
	protected $authorizeUrl = $baseUrl . "/login/oauth/authorize";
	protected $accessTokenUrl = $baseUrl . "/login/oauth/access_token";
	protected $userDataUrl = $baseUrl . "/user";
	protected $scope = array(
        'user:email',
	);

	protected $headers = array(
		'authorize' => array(),
		'access_token' => array(
			'Accept' => 'application/json'
		),
		'user_details' => array(
			'Accept' => 'application/vnd.github.v3'
		),
	);
	
	public function request($uri, $accessToken)
	{
		$uri = ((substr($uri, 0, 1) != '/') ? '/' : '') . $uri;
		return $this->getJson($this->getRequestUrl($baseUrl . $uri . "?access_token=" . $accessToken), []);
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

	protected function requestUserData()
	{
		$userData = parent::requestUserData();
		$userData['email'] = $this->requestEmail();
		return $userData;
	}

	protected function requestEmail()
	{
		$url = $this->getEmailUrl();
		$emails = $this->getJson($url, $this->headers['user_details']);
		return $this->getPrimaryEmail($emails);
	}

	protected function getEmailUrl()
	{
		$url = $this->getUserDataUrl() .'/emails';
		$url .= "?access_token=".$this->accessToken;
		return $url;
	}

	public function getJson($url, $headers)
	{
		$request = $this->httpClient->get($url, $headers);
		$response = $request->send();
		return $response->json();
	}

	protected function getPrimaryEmail($emails)
	{
		foreach ($emails as $email) {
			if ($email['primary']) {
				return $email['email'];
			}
		}
		return $emails[0]['email'];
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
		return $this->getProviderUserData('email');
	}
}
