<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\InvalidAuthorizationCodeException;

class LinkedInProvider extends Provider
{
	protected $authorizeUrl = "https://www.linkedin.com/uas/oauth2/authorization";
	protected $accessTokenUrl = "https://www.linkedin.com/uas/oauth2/accessToken";
	protected $userDataUrl = "https://api.linkedin.com/v1/people/~";
	protected $profileFields = array(
		'id',
		'first-name',
		'last-name',
		'email-address',
		'picture-url',
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
		$queryString = "code=".$this->getAuthorizationCode();
		$queryString .= "&client_id=".$this->clientId;
		$queryString .= "&client_secret=".$this->clientSecret;
		$queryString .= "&redirect_uri=".$this->redirectUri();
		$queryString .= "&grant_type=authorization_code";
		return $this->accessTokenUrl ."?" . $queryString;
	}

	protected function getUserDataUrl()
	{
		return $this->userDataUrl;
	}

	protected function parseTokenResponse($response)
	{
		$response = json_decode($response);
		if (! isset($response->access_token)) {
			throw new InvalidAuthorizationCodeException;
		}
		return $response->access_token;
	}

	protected function buildUserDataUrl()
	{
		$url = $this->getUserDataUrl();
		$url .= ':('.$this->compileProfileFields().')';
		$url .= '?format=json';
		$url .= "&oauth2_access_token=".$this->accessToken;
		return $url;
	}

	protected function compileProfileFields()
	{
		return implode(',', $this->profileFields);
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
		return $this->getProviderUserData('emailAddress');
	}

	protected function firstName()
	{
		return $this->getProviderUserData('firstName');
	}

	protected function lastName()
	{
		return $this->getProviderUserData('lastName');
	}

	protected function imageUrl()
	{
		return $this->getProviderUserData('pictureUrl');
	}

	protected function email()
	{
		return $this->getProviderUserData('emailAddress');
	}
}
