<?php namespace AdamWathan\EloquentOAuth\Providers;

use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

class DisqusProvider extends Provider
{
    protected $authorizeUrl = "https://disqus.com/api/oauth/2.0/authorize";
    protected $accessTokenUrl = "https://disqus.com/api/oauth/2.0/access_token/";
    protected $userDataUrl = "https://disqus.com/api/3.0/users/details.json";
    protected $scope = array(
        'read'
    );
    protected $delimiter = "&";

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
        $queryString = "grant_type=" . urlencode("authorization_code");
        $queryString .= "&client_id=".urlencode($this->clientId);
        $queryString .= "&client_secret=".urlencode($this->clientSecret);
        $queryString .= "&redirect_uri=".urlencode($this->redirectUri());
        $queryString .= "&code=".urlencode($_GET['code']);
        return $this->accessTokenUrl ."?" . $queryString;  
    }

    protected function getUserDataUrl()
    {
        $queryString = "&api_key=".urlencode($this->clientId);
        $queryString .= "&api_secret=".urlencode($this->clientSecret);
        return $this->userDataUrl ."?" . $queryString;          
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
        return $this->providerUserData['response']['id'];
    }

    protected function nickName()
    {
        return $this->providerUserData['response']['username'];
    }

    protected function firstName()
    {
        return $this->providerUserData['response']['name'];
    }

    protected function lastName()
    {
        return $this->providerUserData['response']['name'];
    }

    protected function profileUrl()
    {
        return $this->providerUserData['response']['profileUrl'];
    }

    protected function imageUrl()
    {
        return $this->providerUserData['response']['avatar']['permalink'];
    }

    protected function email()
    {
        return null;
    }
}
