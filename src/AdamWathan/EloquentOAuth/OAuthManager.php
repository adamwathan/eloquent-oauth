<?php namespace AdamWathan\EloquentOAuth;

use Closure;
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
        return $this->redirect->to($this->getProvider($provider)->authorizeUrl());
    }

    protected function getProvider($providerAlias)
    {
        return $this->providers[$providerAlias];
    }

    public function login($provider, Closure $callback = null)
    {
        if (! $details = $this->getProvider($provider)->userDetails()) {
            throw new ApplicationRejectedException;
        }
        $user = $this->updateUser($provider, $details);
        if ($callback) {
            $callback($user, $details);
        }
        $this->auth->login($user);
    }

    protected function updateUser($provider, ProviderUserDetails $details)
    {
        if (! $user = $this->getUser($provider, $details)) {
            $user = $this->createUser();
        }
        $this->updateAccessToken($user, $provider, $details);
        return $user;
    }

    protected function getUser($provider, ProviderUserDetails $details)
    {
        $identity = OAuthIdentity::where('provider', $provider)
            ->where('provider_user_id', $details->userId)
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

    protected function updateAccessToken($user, $provider, ProviderUserDetails $details)
    {
        $this->flushAccessTokens($user, $provider);
        $this->addAccessToken($user, $provider, $details);
    }

    protected function flushAccessTokens($user, $provider)
    {
        OAuthIdentity::where('user_id', $user->getKey())
            ->where('provider', $provider)
            ->delete();
    }

    protected function addAccessToken($user, $provider, ProviderUserDetails $details)
    {
        $identity = new OAuthIdentity;
        $identity->user_id = $user->getKey();
        $identity->provider = $provider;
        $identity->provider_user_id = $details->userId;
        $identity->access_token = $details->accessToken;
        $identity->save();
    }
}