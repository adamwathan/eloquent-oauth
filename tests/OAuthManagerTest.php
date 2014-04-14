<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\OAuthManager;

class OAuthManagerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_authorize_returns_correct_redirect_when_provider_is_registered()
    {
        $auth = M::mock('Illuminate\\Auth\\AuthManager')->shouldIgnoreMissing();
        $redirector = M::mock('Illuminate\\Routing\\Redirector')->shouldIgnoreMissing();
        $stateManager  = M::mock('AdamWathan\\EloquentOAuth\\StateManager')->shouldIgnoreMissing();
        $users  = M::mock('AdamWathan\\EloquentOAuth\\UserRepository')->shouldIgnoreMissing();
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityRepository')->shouldIgnoreMissing();

        $oauth = new OAuthManager($auth, $redirector, $stateManager, $users, $identities);

        $redirectResponse = M::mock('Illuminate\\Http\\RedirectResponse');
        $redirectUrl = 'http://example.com/authorize';
        $provider = M::mock('AdamWathan\\EloquentOAuth\\Providers\\ProviderInterface');
        $provider->shouldReceive('authorizeUrl')->andReturn($redirectUrl);
        $redirector->shouldReceive('to')->with($redirectUrl)->andReturn($redirectResponse);

        $oauth->registerProvider('provider', $provider);

        $result = $oauth->authorize('provider');
        $expected = $redirectResponse;

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException AdamWathan\EloquentOAuth\Exceptions\ProviderNotRegisteredException
     */
    public function test_authorize_throws_exception_when_provider_is_not_registered()
    {
        $auth = M::mock('Illuminate\\Auth\\AuthManager')->shouldIgnoreMissing();
        $redirector = M::mock('Illuminate\\Routing\\Redirector')->shouldIgnoreMissing();
        $stateManager  = M::mock('AdamWathan\\EloquentOAuth\\StateManager')->shouldIgnoreMissing();
        $users  = M::mock('AdamWathan\\EloquentOAuth\\UserRepository')->shouldIgnoreMissing();
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityRepository')->shouldIgnoreMissing();

        $oauth = new OAuthManager($auth, $redirector, $stateManager, $users, $identities);

        $result = $oauth->authorize('missingProvider');
    }

    public function test_login_creates_new_user_if_no_matching_user_exists()
    {
        $auth = M::mock('Illuminate\\Auth\\AuthManager');
        $redirector = M::mock('Illuminate\\Routing\\Redirector');
        $stateManager  = M::mock('AdamWathan\\EloquentOAuth\\StateManager')->shouldIgnoreMissing();
        $users  = M::mock('AdamWathan\\EloquentOAuth\\UserRepository');
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityRepository')->shouldIgnoreMissing();

        $provider = M::mock('AdamWathan\\EloquentOAuth\\Providers\\ProviderInterface');
        $userDetails = M::mock('AdamWathan\\EloquentOAuth\\ProviderUserDetails');

        $user = M::mock('stdClass')->shouldIgnoreMissing();

        $oauth = new OAuthManager($auth, $redirector, $stateManager, $users, $identities);
        $oauth->registerProvider('provider', $provider);

        $stateManager->shouldReceive('verifyState')->andReturn(true);
        $provider->shouldReceive('getUserDetails')->andReturn($userDetails);
        $users->shouldReceive('create')->andReturn($user);

        $auth->shouldReceive('login')->with($user)->once();
        $result = $oauth->login('provider');
    }

    public function test_login_uses_existing_user_if_matching_user_exists()
    {
        $auth = M::mock('Illuminate\\Auth\\AuthManager');
        $redirector = M::mock('Illuminate\\Routing\\Redirector');
        $stateManager  = M::mock('AdamWathan\\EloquentOAuth\\StateManager')->shouldIgnoreMissing();
        $users  = M::mock('AdamWathan\\EloquentOAuth\\UserRepository');
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityRepository')->shouldIgnoreMissing();

        $provider = M::mock('AdamWathan\\EloquentOAuth\\Providers\\ProviderInterface');
        $freshUserDetails = M::mock('AdamWathan\\EloquentOAuth\\ProviderUserDetails');
        $existingUserDetails = M::mock('AdamWathan\\EloquentOAuth\\ProviderUserDetails');

        $user = M::mock('stdClass')->shouldIgnoreMissing();

        $oauth = new OAuthManager($auth, $redirector, $stateManager, $users, $identities);
        $oauth->registerProvider('provider', $provider);

        $stateManager->shouldReceive('verifyState')->andReturn(true);
        $provider->shouldReceive('getUserDetails')->andReturn($freshUserDetails);
        $identities->shouldReceive('getByProvider')->with('provider', $freshUserDetails)->andReturn($existingUserDetails);
        $users->shouldReceive('create')->never();
        $users->shouldReceive('findByIdentity')->once()->andReturn($user);

        $auth->shouldReceive('login')->with($user)->once();
        $result = $oauth->login('provider');
    }
}
