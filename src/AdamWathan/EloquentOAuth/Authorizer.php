<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Routing\Redirector as Redirect;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class Authorizer
{
    protected $redirect;

    public function __construct(Redirect $redirect)
    {
        $this->redirect = $redirect;
    }

    public function authorize(ProviderInterface $provider, $state)
    {
        return $this->redirect->to($provider->authorizeUrl($state));
    }
}
