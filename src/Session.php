<?php namespace AdamWathan\EloquentOAuth;

use SocialNorm\Session as SocialNormSession;

class Session implements SocialNormSession
{
    private $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function get($key)
    {
        return $this->store->get($key);
    }

    public function put($key, $value)
    {
        return $this->store->put($key, $value);
    }
}
