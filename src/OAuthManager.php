<?php namespace AdamWathan\EloquentOAuth;

use Closure;
use Illuminate\Routing\Redirector;
use SocialNorm\SocialNorm;

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

    public function authorize($providerAlias)
    {
        return $this->redirect->to($this->socialnorm->authorize($providerAlias));
    }

    public function login($providerAlias, Closure $callback = null)
    {
        $details = $this->socialnorm->getUser($providerAlias);
        return $this->authenticator->login($providerAlias, $details, $callback);
    }
}
