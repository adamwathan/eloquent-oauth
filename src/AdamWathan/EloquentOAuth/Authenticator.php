<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Auth\AuthManager as Auth;
use AdamWathan\EloquentOAuth\Exceptions\ProviderNotRegisteredException;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class Authenticator
{
    protected $auth;
    protected $stateManager;
    protected $users;
    protected $identities;

    public function __construct(Auth $auth, StateManager $stateManager, UserStore $users, IdentityStore $identities)
    {
        $this->auth = $auth;
        $this->stateManager = $stateManager;
        $this->users = $users;
        $this->identities = $identities;
    }

    public function login($providerAlias, $provider, Closure $callback = null)
    {
        $this->verifyState();
        $details = $provider->getUserDetails();
        $user = $this->getUser($providerAlias, $details);
        if ($callback) {
            $callback($user, $details);
        }
        $this->updateUser($user, $provider, $details);
        $this->auth->login($user);
        return $user;
    }

    protected function verifyState()
    {
        if (! $this->stateManager->verifyState()) {
            throw new InvalidAuthorizationCodeException;
        }
    }

    protected function getUser($provider, $details)
    {
        if ($this->userExists($provider, $details)) {
            $user = $this->getExistingUser($provider, $details);
        } else {
            $user = $this->createUser();
        }
        return $user;
    }

    protected function updateUser($user, $provider, $details)
    {
        $this->users->store($user);
        $this->updateAccessToken($user, $provider, $details);
    }

    protected function userExists($provider, ProviderUserDetails $details)
    {
        return $this->identities->userExists($provider, $details);
    }

    protected function getExistingUser($provider, $details)
    {
        $identity = $this->getIdentity($provider, $details);
        return $this->users->findByIdentity($identity);
    }

    protected function getIdentity($provider, ProviderUserDetails $details)
    {
        return $this->identities->getByProvider($provider, $details);
    }

    protected function createUser()
    {
        $user = $this->users->create();
        return $user;
    }

    protected function updateAccessToken($user, $provider, ProviderUserDetails $details)
    {
        $this->flushAccessTokens($user, $provider);
        $this->addAccessToken($user, $provider, $details);
    }

    protected function flushAccessTokens($user, $provider)
    {
        $this->identities->flush($user, $provider);
    }

    protected function addAccessToken($user, $provider, ProviderUserDetails $details)
    {
        $identity = new OAuthIdentity;
        $identity->user_id = $user->getKey();
        $identity->provider = $provider;
        $identity->provider_user_id = $details->userId;
        $identity->access_token = $details->accessToken;
        $this->identities->store($identity);
    }
}
