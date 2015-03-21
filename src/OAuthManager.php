<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;
use Illuminate\Routing\Redirector;
use SocialNorm\SocialNorm;
use SocialNorm\Exceptions\InvalidAuthorizationCodeException as SocialNormInvalidCode;
use SocialNorm\Provider;

class OAuthManager
{
    protected $redirect;
    protected $authenticator;
    protected $socialnorm;

    public function __construct(Redirector $redirect, Authenticator $authenticator, SocialNorm $socialnorm)
    {
        $this->redirect = $redirect;
        $this->authenticator = $authenticator;
        $this->socialnorm = $socialnorm;
    }

    public function registerProvider($alias, Provider $provider)
    {
        $this->socialnorm->registerProvider($alias, $provider);
    }

    public function authorize($providerAlias)
    {
        return $this->redirect->to($this->socialnorm->authorize($providerAlias));
    }

    public function login($providerAlias, Closure $callback = null)
    {
        try {
            $details = $this->socialnorm->getUser($providerAlias);
            return $this->authenticator->login($providerAlias, $details, $callback);
        } catch (SocialNormInvalidCode $e) {
            throw new InvalidAuthorizationCodeException;
        }
    }
}
