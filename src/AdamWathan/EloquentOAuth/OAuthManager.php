<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Auth\AuthManager as Auth;
use Illuminate\Routing\Redirector as Redirect;
use Illuminate\Session\Store as Session;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class OAuthManager
{
    protected $auth;
    protected $model;
    protected $redirect;
    protected $session;
    protected $identities;
    protected $state;
    protected $providers = array();

    public function __construct(Auth $auth, $model, Redirect $redirect, Session $session, IdentityRepository $identities)
    {
        $this->auth = $auth;
        $this->model = $model;
        $this->redirect = $redirect;
        $this->session = $session;
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

    protected function generateState()
    {
        $this->setState($state = str_random());
        return $state;
    }

    protected function setState($state)
    {
        $this->session->put('oauth.state', $state);
    }

    protected function getState()
    {
        return $this->session->get('oauth.state');
    }

    protected function getProvider($providerAlias)
    {
        return $this->providers[$providerAlias];
    }

    public function login($provider, Closure $callback = null)
    {
        $this->verifyState();
        $details = $this->getUserDetails($provider);
        $user = $this->getUser($provider, $details);
        if ($callback) {
            $callback($user, $details);
        }
        $this->auth->login($user);
    }

    protected function verifyState()
    {
        if (! isset($_GET['state']) || $_GET['state'] !== $this->getState()) {
            throw new InvalidAuthorizationCodeException;
        }
    }

    protected function getUser($provider, $details)
    {
        if ($this->userExists($provider, $details)) {
            $user = $this->updateUser($provider, $details);
        } else {
            $user = $this->createUser($provider, $details);
        }
        return $user;
    }

    protected function getUserDetails($provider)
    {
        return $this->getProvider($provider)->getUserDetails();
    }

    protected function userExists($provider, ProviderUserDetails $details)
    {
        return (bool) $this->getIdentity($provider, $details);
    }

    protected function getIdentity($provider, ProviderUserDetails $details)
    {
        return $this->identities->getByProvider($provider, $details->userId);
    }

    protected function updateUser($provider, ProviderUserDetails $details)
    {
        $identity = $this->getIdentity($provider, $details);
        $user = $identity->belongsTo($this->model, 'user_id')->first();
        $this->updateAccessToken($user, $provider, $details);
        return $user;
    }

    protected function createUser($provider, ProviderUserDetails $details)
    {
        $user = new $this->model;
        $user->save();
        $this->addAccessToken($user, $provider, $details);
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
