<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Session\Store as Session;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

class StateManager
{
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function generateState()
    {
        $this->store($state = str_random());
        return $state;
    }

    public function store($state)
    {
        $this->session->put('oauth.state', $state);
    }

    public function retrieveState()
    {
        return $this->session->get('oauth.state');
    }

    public function verifyState()
    {
        if (! isset($_GET['state']) || $_GET['state'] !== $this->retrieveState()) {
            throw new InvalidAuthorizationCodeException;
        }
    }
}
