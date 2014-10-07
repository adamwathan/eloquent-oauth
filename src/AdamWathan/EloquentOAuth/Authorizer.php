<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Routing\Redirector as Redirect;
use AdamWathan\EloquentOAuth\Providers\ProviderInterface;

class Authorizer
{
    protected $redirect;
    protected $stateManager;

    public function __construct(Redirect $redirect, StateManager $stateManager)
    {
        $this->redirect = $redirect;
        $this->stateManager = $stateManager;
    }

    public function authorize(ProviderInterface $provider)
    {
        $state = $this->generateState();
        return $this->redirect->to($provider->authorizeUrl($state));
    }

    protected function generateState()
    {
        return $this->stateManager->generateState();
    }
}
