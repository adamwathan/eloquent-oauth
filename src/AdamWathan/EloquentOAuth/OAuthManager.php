<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Auth\AuthManager as Auth;
use Illuminate\Routing\Redirector as Redirect;
use AdamWathan\EloquentOAuth\Providers\Provider;

class OAuthManager
{
    protected $auth;
    protected $model;
    protected $redirect;
    protected $providers = array();

    public function __construct(Auth $auth, $model, Redirect $redirect)
    {
        $this->auth = $auth;
        $this->model = $model;
        $this->redirect = $redirect;
    }

    public function registerProvider($alias, Provider $provider)
    {
        $this->providers[$alias] = $provider;
    }

    public function authorize($provider)
    {
        return $this->redirect->to($this->providers[$provider]->authorizeUrl());
    }

    public function login($provider)
    {
        if (! $accessToken = $this->providers[$provider]->getAccessToken()) {
            throw new ApplicationRejectedException;
        }
        if (! $providerUserId = $this->providers[$provider]->getUserId()) {
            throw new ApplicationRejectedException;
        }
        $user = $this->updateUser($provider, $providerUserId, $accessToken);
        $this->auth->login($user);
    }

    protected function updateUser($provider, $providerUserId, $accessToken)
    {
        if (! $user = $this->getUser($provider, $providerUserId)) {
            $user = $this->createUser($provider, $providerUserId, $accessToken);
        }
        $this->updateAccessToken($user, $provider, $providerUserId, $accessToken);
        return $user;
    }

    protected function getUser($provider, $providerUserId)
    {
        $identity = OAuthIdentity::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
        if (! $identity) {
            return null;
        }
        $user = $identity->belongsTo($this->model, 'user_id')->first();
        return $user;
    }

    protected function createUser()
    {
        $user = new $this->model;
        $user->save();
        return $user;
    }

    protected function updateAccessToken($user, $provider, $providerUserId, $accessToken)
    {
        $this->flushAccessTokens($user, $provider);
        $this->addAccessToken($user, $provider, $providerUserId, $accessToken);
    }

    protected function flushAccessTokens($user, $provider)
    {
        OAuthIdentity::where('user_id', $user->getKey())
            ->where('provider', $provider)
            ->delete();
    }

    protected function addAccessToken($user, $provider, $providerUserId, $accessToken)
    {
        $identity = new OAuthIdentity;
        $identity->user_id = $user->getKey();
        $identity->provider = $provider;
        $identity->provider_user_id = $providerUserId;
        $identity->access_token = $accessToken;
        $identity->save();
    }
}