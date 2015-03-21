<?php namespace AdamWathan\EloquentOAuth;

use SocialNorm\User as UserDetails;

class IdentityStore
{
    public function getByProvider($provider, UserDetails $providerUserDetails)
    {
        return OAuthIdentity::where('provider', $provider)
            ->where('provider_user_id', $providerUserDetails->id)
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

    public function userExists($provider, UserDetails $details)
    {
        return (bool) $this->getByProvider($provider, $details);
    }
}
