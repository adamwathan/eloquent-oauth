<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Auth\AuthManager as Auth;
use Illuminate\Routing\Redirector as Redirect;
use AdamWathan\EloquentOAuth\Exceptions\ProviderNotRegisteredException;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class OAuthManager
{
    protected $auth;
    protected $redirect;
    protected $stateManager;
    protected $users;
    protected $identities;
    protected $providers = array();

    public function __construct(Auth $auth, Redirect $redirect, StateManager $stateManager, UserStore $users, IdentityStore $identities)
    {
        $this->auth = $auth;
        $this->redirect = $redirect;
        $this->stateManager = $stateManager;
        $this->users = $users;
        $this->identities = $identities;
    }

    public function registerProvider($alias, ProviderInterface $provider)
    {
        $this->providers[$alias] = $provider;
    }

    public function authorize($provider)
    {
        $state = $this->generateState();
        return $this->redirect->to($this->getProvider($provider)->authorizeUrl($state));
    }

    public function login($provider, Closure $callback = null)
    {
        $this->verifyState();
        $details = $this->getUserDetails($provider);
        $user = $this->getUser($provider, $details);
        if ($callback) {
            $callback($user, $details);
        }
        $this->updateUser($user, $provider, $details);
        $this->auth->login($user);
        return $user;
    }

    protected function generateState()
    {
        return $this->stateManager->generateState();
    }

    public function getProvider($providerAlias)
    {
        if (! $this->hasProvider($providerAlias)) {
            throw new ProviderNotRegisteredException("No provider has been registered under the alias '{$providerAlias}'");
        }
        return $this->providers[$providerAlias];
    }

    protected function hasProvider($alias)
    {
        return isset($this->providers[$alias]);
    }

    protected function verifyState()
    {
        if (! $this->stateManager->verifyState()) {
            throw new InvalidAuthorizationCodeException;
        }
    }

    protected function getUserDetails($provider)
    {
        return $this->getProvider($provider)->getUserDetails();
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
        return (bool) $this->getIdentity($provider, $details);
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
