<?php namespace AdamWathan\EloquentOAuth;

class IdentityRepository
{
    public function getByProvider($provider, $userId)
    {
        return OAuthIdentity::where('provider', $provider)
            ->where('provider_user_id', $userId)
            ->first();
    }

    public function flush($user, $provider)
    {
        OAuthIdentity::where('user_id', $user->getKey())
            ->where('provider', $provider)
            ->delete();
    }

    public function store(OAuthIdentity $identity)
    {
        $identity->save();
    }
}