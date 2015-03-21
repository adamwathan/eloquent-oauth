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
        $auth = M::mock('Illuminate\\Auth\\AuthManager');
        $users  = M::mock('AdamWathan\\EloquentOAuth\\UserStore');
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityStore')->shouldIgnoreMissing();

        $userDetails = M::mock('AdamWathan\\EloquentOAuth\\ProviderUserDetails');

        $user = M::mock('stdClass')->shouldIgnoreMissing();

        $authenticator = new Authenticator($auth, $users, $identities);

        $users->shouldReceive('create')->andReturn($user);
        $users->shouldReceive('store')->once();
        $auth->shouldReceive('login')->with($user)->once();

        $authenticator->login('provider', $userDetails);
    }

    public function test_login_uses_existing_user_if_matching_user_exists()
    {
        $auth = M::mock('Illuminate\\Auth\\AuthManager');
        $users  = M::mock('AdamWathan\\EloquentOAuth\\UserStore')->shouldIgnoreMissing();
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityStore')->shouldIgnoreMissing();

        $userDetails = M::mock('AdamWathan\\EloquentOAuth\\ProviderUserDetails');
        $identity = new OAuthIdentity;

        $user = M::mock('stdClass')->shouldIgnoreMissing();

        $authenticator = new Authenticator($auth, $users, $identities);

        $identities->shouldReceive('userExists')->andReturn(true);
        $identities->shouldReceive('getByProvider')->andReturn($identity);
        $users->shouldReceive('create')->never();
        $users->shouldReceive('findByIdentity')->andReturn($user);
        $auth->shouldReceive('login')->with($user)->once();

        $authenticator->login('provider', $userDetails);
    }
}
