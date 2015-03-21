<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Contracts\Auth\Guard as Auth;
use SocialNorm\User as UserDetails;

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

    public function login($providerAlias, UserDetails $userDetails, Closure $callback = null)
    {
        $user = $this->getUser($providerAlias, $userDetails);
        if ($callback) {
            $callback($user, $userDetails);
        }
        $this->updateUser($user, $providerAlias, $userDetails);
        $this->auth->login($user);
    }

    protected function getUser($providerAlias, UserDetails $details)
    {
        if ($this->identities->userExists($providerAlias, $details)) {
            return $this->getExistingUser($providerAlias, $details);
        }
        return $this->users->create();
    }

    protected function updateUser($user, $providerAlias, UserDetails $details)
    {
        $this->users->store($user);
        $this->storeProviderIdentity($user, $providerAlias, $details);
    }

    protected function getExistingUser($providerAlias, UserDetails $details)
    {
        $identity = $this->identities->getByProvider($providerAlias, $details);
        return $this->users->findByIdentity($identity);
    }

    protected function storeProviderIdentity($user, $providerAlias, UserDetails $details)
    {
        if ($this->identities->userExists($providerAlias, $details)) {
            $this->updateProviderIdentity($providerAlias, $details);
        } else {
            $this->addProviderIdentity($user, $providerAlias, $details);
        }
    }

    protected function updateProviderIdentity($providerAlias, UserDetails $details)
    {
        $identity = $this->identities->getByProvider($providerAlias, $details);
        $identity->access_token = $details->access_token;
        $this->identities->store($identity);
    }

    protected function addProviderIdentity($user, $providerAlias, UserDetails $details)
    {
        $identity = new OAuthIdentity;
        $identity->user_id = $user->getKey();
        $identity->provider = $providerAlias;
        $identity->provider_user_id = $details->id;
        $identity->access_token = $details->access_token;
        $this->identities->store($identity);
    }
}
