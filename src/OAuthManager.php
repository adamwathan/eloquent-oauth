<?php namespace AdamWathan\EloquentOAuth;

class OAuthManager
{
    protected $redirect;
    protected $authenticator;
    protected $socialnorm;

    public function __construct($redirect, $authenticator, $socialnorm)
    {
        $this->redirect = $redirect;
        $this->authenticator = $authenticator;
        $this->socialnorm = $socialnorm;
    }

    public function authorize($providerAlias)
    {
        return $this->redirect->to($this->socialnorm->authorize($providerAlias));
    }

    public function login($providerAlias, $callback = null)
    {
        $details = $this->socialnorm->getUser($providerAlias);
        return $this->authenticator->login($providerAlias, $details, $callback);
    }

    public function registerProvider($alias, $provider)
    {
        $this->socialnorm->registerProvider($alias, $provider);
    }
}
