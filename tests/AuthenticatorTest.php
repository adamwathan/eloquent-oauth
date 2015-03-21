<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\Authenticator;
use AdamWathan\EloquentOAuth\OAuthIdentity;

class AuthenticatorTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_login_creates_new_user_if_no_matching_user_exists()
    {
        $auth = M::mock('Illuminate\Contracts\Auth\Guard');
        $users  = M::mock('AdamWathan\EloquentOAuth\UserStore');
        $identities  = M::mock('AdamWathan\EloquentOAuth\IdentityStore')->shouldIgnoreMissing();

        $userDetails = M::mock('SocialNorm\User');

        $user = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();

        $authenticator = new Authenticator($auth, $users, $identities);

        $users->shouldReceive('create')->andReturn($user);
        $users->shouldReceive('store')->once();
        $auth->shouldReceive('login')->with($user)->once();

        $authenticator->login('provider', $userDetails);
    }

    public function test_login_uses_existing_user_if_matching_user_exists()
    {
        $auth = M::mock('Illuminate\Contracts\Auth\Guard');
        $users  = M::mock('AdamWathan\EloquentOAuth\UserStore')->shouldIgnoreMissing();
        $identities  = M::mock('AdamWathan\EloquentOAuth\IdentityStore')->shouldIgnoreMissing();

        $userDetails = M::mock('SocialNorm\User');
        $identity = new OAuthIdentity;

        $user = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();

        $authenticator = new Authenticator($auth, $users, $identities);

        $identities->shouldReceive('userExists')->andReturn(true);
        $identities->shouldReceive('getByProvider')->andReturn($identity);
        $users->shouldReceive('create')->never();
        $users->shouldReceive('findByIdentity')->andReturn($user);
        $auth->shouldReceive('login')->with($user)->once();

        $authenticator->login('provider', $userDetails);
    }
}
