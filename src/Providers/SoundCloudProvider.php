<?php namespace AdamWathan\EloquentOAuth\Providers;

class SoundCloudProvider extends Provider
{
    protected $authorizeUrl = "https://soundcloud.com/connect";
    protected $accessTokenUrl = "https://api.soundcloud.com/oauth2/token";
    protected $userDataUrl = "https://api.soundcloud.com/me.json";
    protected $scope = [
        'non-expiring',
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

    protected function buildUserDataUrl()
    {
        $url = $this->getUserDataUrl();
        $url .= "?oauth_token=".$this->accessToken;
        return $url;
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
        return $this->getProviderUserData('username');
    }

    protected function firstName()
    {
        return strstr($this->getProviderUserData('full_name'), ' ', true) ?: null;
    }

    protected function lastName()
    {
        return substr(strstr($this->getProviderUserData('full_name'), ' '), 1) ?: null;
    }

    protected function imageUrl()
    {
        return $this->getProviderUserData('avatar_url');
    }

    protected function email()
    {
        return null; // Impossible to get email from SoundCloud
    }
}
