<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\Authenticator;
use AdamWathan\EloquentOAuth\OAuthIdentity;
use SocialNorm\User as SocialNormUser;

class AuthenticatorTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_login_creates_new_user_if_no_matching_user_exists()
    {
        $providerAlias = 'provider';
        $auth = M::spy();
        $users  = M::spy();
        $identities  = M::spy();
        $userDetails = new SocialNormUser([]);
        $user = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();

        $identities->shouldReceive('userExists')->andReturn(false);
        $users->shouldReceive('create')->andReturn($user);

        $authenticator = new Authenticator($auth, $users, $identities);
        $authenticator->login('provider', $userDetails);

        $users->shouldHaveReceived('create');
        $users->shouldHaveReceived('store')->with($user);
        $identities->shouldHaveReceived('store');
        $auth->shouldHaveReceived('login')->with($user);
    }

    public function test_login_uses_existing_user_if_matching_user_exists()
    {
        $providerAlias = 'provider';

        $userDetails = new SocialNormUser([]);
        $user = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();

        $auth = M::spy();

        $users  = M::spy([
            'findByIdentity' => $user
        ]);

        $identities  = M::spy([
            'userExists' => true,
            'getByProvider' => new OAuthIdentity,
        ]);

        $authenticator = new Authenticator($auth, $users, $identities);
        $authenticator->login('provider', $userDetails);

        $users->shouldNotHaveReceived('create');
        $users->shouldHaveReceived('store')->with($user);
        $identities->shouldHaveReceived('store');
        $auth->shouldHaveReceived('login')->with($user);
    }

    public function test_if_a_user_is_returned_from_the_callback_that_user_is_used()
    {
        $providerAlias = 'provider';

        $userDetails = new SocialNormUser([]);
        $user = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();
        $otherUser = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();

        $auth = M::spy();

        $users  = M::spy([
            'findByIdentity' => $user
        ]);

        $identities  = M::spy([
            'userExists' => true,
            'getByProvider' => new OAuthIdentity,
        ]);

        $authenticator = new Authenticator($auth, $users, $identities);
        $authenticator->login('provider', $userDetails, function () use ($otherUser) {
            return $otherUser;
        });

        $users->shouldNotHaveReceived('create');
        $users->shouldHaveReceived('store')->with($otherUser);
        $identities->shouldHaveReceived('store');
        $auth->shouldHaveReceived('login')->with($otherUser);
    }

    public function test_if_nothing_is_returned_from_the_callback_the_found_or_created_user_is_used()
    {
        $providerAlias = 'provider';

        $userDetails = new SocialNormUser([]);
        $foundUser = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();
        $otherUser = M::mock('Illuminate\Contracts\Auth\Authenticatable')->shouldIgnoreMissing();

        $auth = M::spy();

        $users  = M::spy([
            'findByIdentity' => $foundUser
        ]);

        $identities  = M::spy([
            'userExists' => true,
            'getByProvider' => new OAuthIdentity,
        ]);

        $authenticator = new Authenticator($auth, $users, $identities);
        $authenticator->login('provider', $userDetails, function () {
            return;
        });

        $users->shouldNotHaveReceived('create');
        $users->shouldHaveReceived('store')->with($foundUser);
        $identities->shouldHaveReceived('store');
        $auth->shouldHaveReceived('login')->with($foundUser);
    }
}
