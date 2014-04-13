<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Session\Store as Session;
use Illuminate\Http\Request;
use AdamWathan\EloquentOAuth\Exceptions\InvalidAuthorizationCodeException;

class StateManager
{
    protected $session;
    protected $input;

    public function __construct(Session $session, Request $input)
    {
        $this->session = $session;
        $this->input = $input;
    }

    public function generateState()
    {
        $this->store($state = str_random());
        return $state;
    }

    protected function store($state)
    {
        $this->session->put('oauth.state', $state);
    }

    public function verifyState()
    {
        if (! $this->input->has('state')) {
            return false;
        }
        return $this->input->get('state') === $this->retrieveState();
    }

    protected function retrieveState()
    {
        return $this->session->get('oauth.state');
    }
}
