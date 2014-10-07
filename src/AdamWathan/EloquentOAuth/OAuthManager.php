<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Auth\AuthManager as Auth;
use AdamWathan\EloquentOAuth\Exceptions\ProviderNotRegisteredException;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class OAuthManager
{
    protected $authorizer;
    protected $providers;
    protected $authenticator;

    public function __construct(Authorizer $authorizer, ProviderRegistrar $providers, Authenticator $authenticator)
    {
        $this->authorizer = $authorizer;
        $this->providers = $providers;
        $this->authenticator = $authenticator;
    }

    public function registerProvider($alias, ProviderInterface $provider)
    {
        $this->providers->registerProvider($alias, $provider);
    }

    public function authorize($providerAlias)
    {
        return $this->authorizer->authorize($this->getProvider($providerAlias));
    }

    public function login($providerAlias, Closure $callback = null)
    {
        return $this->authenticator->login($providerAlias, $this->getProvider($providerAlias), $callback);
    }

    protected function getProvider($providerAlias)
    {
        return $this->providers->getProvider($providerAlias);
    }
}
