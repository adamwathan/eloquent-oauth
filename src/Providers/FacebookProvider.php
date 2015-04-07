<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

class FacebookProvider extends Provider
{
    protected $authorizeUrl = "https://www.facebook.com/dialog/oauth";
    protected $accessTokenUrl = "https://graph.facebook.com/v2.3/oauth/access_token";
    protected $userDataUrl = "https://graph.facebook.com/v2.3/me";
    protected $scope = [
        'email',
    ];

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

    protected function requestAccessToken()
    {
        $url = $this->getAccessTokenBaseUrl();
        try {
            $response = $this->httpClient->get($url, [
                'query' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectUri(),
                    'code' => $this->getAuthorizationCode(),
                ],
            ]);
        } catch (BadResponseException $e) {
            throw new InvalidAuthorizationCodeException((string) $e->getResponse());
        }
        return $this->parseTokenResponse((string) $response->getBody());
    }

    protected function parseTokenResponse($response)
    {
        return $this->parseJsonTokenResponse($response);
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
        return $this->getProviderUserData('name');
    }

    protected function firstName()
    {
        return $this->getProviderUserData('first_name');
    }

    protected function lastName()
    {
        return $this->getProviderUserData('last_name');
    }

    protected function imageUrl()
    {
        return 'https://graph.facebook.com/v2.3/'.$this->userId().'/picture';
    }

    protected function email()
    {
        return $this->getProviderUserData('email');
    }
}
