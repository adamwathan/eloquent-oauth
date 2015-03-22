<?php namespace AdamWathan\EloquentOAuth;

class Authenticator
{
    protected $auth;
    protected $users;
    protected $identities;

    public function __construct($auth, $users, $identities)
    {
        $this->auth = $auth;
        $this->users = $users;
        $this->identities = $identities;
    }

    public function login($providerAlias, $userDetails, $callback = null)
    {
        $user = $this->getUser($providerAlias, $userDetails);
        if ($callback) {
            $callback($user, $userDetails);
        }
        $this->updateUser($user, $providerAlias, $userDetails);
        $this->auth->login($user);
    }

    protected function getUser($providerAlias, $details)
    {
        if ($this->identities->userExists($providerAlias, $details)) {
            return $this->getExistingUser($providerAlias, $details);
        }
        return $this->users->create();
    }

    protected function updateUser($user, $providerAlias, $details)
    {
        $this->users->store($user);
        $this->storeProviderIdentity($user, $providerAlias, $details);
    }

    protected function getExistingUser($providerAlias, $details)
    {
        $identity = $this->identities->getByProvider($providerAlias, $details);
        return $this->users->findByIdentity($identity);
    }

    protected function storeProviderIdentity($user, $providerAlias, $details)
    {
        if ($this->identities->userExists($providerAlias, $details)) {
            $this->updateProviderIdentity($providerAlias, $details);
        } else {
            $this->addProviderIdentity($user, $providerAlias, $details);
        }
    }

    protected function updateProviderIdentity($providerAlias, $details)
    {
        $identity = $this->identities->getByProvider($providerAlias, $details);
        $identity->access_token = $details->access_token;
        $this->identities->store($identity);
    }

    protected function addProviderIdentity($user, $providerAlias, $details)
    {
        $identity = new OAuthIdentity;
        $identity->user_id = $user->getKey();
        $identity->provider = $providerAlias;
        $identity->provider_user_id = $details->id;
        $identity->access_token = $details->access_token;
        $this->identities->store($identity);
    }
}
