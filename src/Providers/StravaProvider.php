<?php namespace AdamWathan\EloquentOAuth\Providers;

class StravaProvider extends Provider
{
    protected $authorizeUrl = "https://www.strava.com/oauth/authorize";
    protected $accessTokenUrl = "https://www.strava.com/oauth/token";
    protected $userDataUrl = "https://www.strava.com/api/v3/athlete";
    protected $scope = [
        'public'
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
        return $this->getProviderUserData('email');
    }

    protected function firstName()
    {
        return $this->getProviderUserData('firstname');
    }

    protected function lastName()
    {
        return $this->getProviderUserData('lastname');
    }

    protected function imageUrl()
    {
        return $this->getProviderUserData('profile');
    }

    protected function email()
    {
        return $this->getProviderUserData('email');
    }
}
