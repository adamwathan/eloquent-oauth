<?php namespace AdamWathan\EloquentOAuth;

class IdentityStore
{
    public function getByProvider($provider, $providerUser)
    {
        return OAuthIdentity::where('provider', $provider)
            ->where('provider_user_id', $providerUser->id)
            ->first();
    }

    public function flush($user, $provider)
    {
        OAuthIdentity::where('user_id', $user->getKey())
            ->where('provider', $provider)
            ->delete();
    }

    public function store($identity)
    {
        $identity->save();
    }

    public function userExists($provider, $providerUser)
    {
        return (bool) $this->getByProvider($provider, $providerUser);
    }
}
