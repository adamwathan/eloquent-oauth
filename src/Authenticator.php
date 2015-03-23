<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Auth\AuthManager as Auth;
use AdamWathan\EloquentOAuth\Exceptions\ProviderNotRegisteredException;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class Authenticator
{
    protected $auth;
    protected $users;
    protected $identities;

    public function __construct(Auth $auth, UserStore $users, IdentityStore $identities)
    {
        $this->auth = $auth;
        $this->users = $users;
        $this->identities = $identities;
    }

    public function login($providerAlias, $userDetails, Closure $callback = null)
    {
        $user = $this->getUser($providerAlias, $userDetails);
        if ($callback) {
            $callback($user, $userDetails);
        }
        $this->updateUser($user, $providerAlias, $userDetails);
        $this->auth->login($user);
    }

    protected function getUser($provider, $details)
    {
        if ($this->identities->userExists($provider, $details)) {
            return $this->getExistingUser($provider, $details);
        } elseif ($this->users->userExists($details)) {
            return $this->users->getExistingUser($details);
        }
        return $this->users->create();
    }

    protected function updateUser($user, $provider, $details)
    {
        $this->users->store($user);
        $this->storeProviderIdentity($user, $provider, $details);
    }

    protected function getExistingUser($provider, $details)
    {
        $identity = $this->identities->getByProvider($provider, $details);
        return $this->users->findByIdentity($identity);
    }

    protected function storeProviderIdentity($user, $provider, ProviderUserDetails $details)
    {
        if ($this->identities->userExists($provider, $details)) {
            $this->updateProviderIdentity($provider, $details);
        } else {
            $this->addProviderIdentity($user, $provider, $details);
        }
    }

    protected function updateProviderIdentity($provider, ProviderUserDetails $details)
    {
        $identity = $this->identities->getByProvider($provider, $details);
        $identity->access_token = $details->accessToken;
        $this->identities->store($identity);
    }

    protected function addProviderIdentity($user, $provider, ProviderUserDetails $details)
    {
        $identity = new OAuthIdentity;
        $identity->user_id = $user->getKey();
        $identity->provider = $provider;
        $identity->provider_user_id = $details->userId;
        $identity->access_token = $details->accessToken;
        $this->identities->store($identity);
    }
}
