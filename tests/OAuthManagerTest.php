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
        $model = 'User';
        $redirector = M::mock('Illuminate\\Routing\\Redirector')->shouldIgnoreMissing();
        $session = M::mock('Illuminate\\Session\\Store')->shouldIgnoreMissing();
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityRepository')->shouldIgnoreMissing();

        $oauth = new OAuthManager($auth, $model, $redirector, $session, $identities);

        $redirectResponse = M::mock('Illuminate\\Http\\RedirectResponse');
        $redirectUrl = 'http://example.com/authorize';
        $providerMock = M::mock('AdamWathan\\EloquentOAuth\\Providers\\ProviderInterface');
        $providerMock->shouldReceive('authorizeUrl')->andReturn($redirectUrl);
        $redirector->shouldReceive('to')->with($redirectUrl)->andReturn($redirectResponse);

        $oauth->registerProvider('provider', $providerMock);

        $result = $oauth->authorize('provider');
        $expected = $redirectResponse;

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException AdamWathan\EloquentOAuth\ProviderNotRegisteredException
     */
    public function test_authorize_throws_exception_when_provider_is_not_registered()
    {
        $auth = M::mock('Illuminate\\Auth\\AuthManager')->shouldIgnoreMissing();
        $model = 'User';
        $redirector = M::mock('Illuminate\\Routing\\Redirector')->shouldIgnoreMissing();
        $session = M::mock('Illuminate\\Session\\Store')->shouldIgnoreMissing();
        $identities  = M::mock('AdamWathan\\EloquentOAuth\\IdentityRepository')->shouldIgnoreMissing();

        $oauth = new OAuthManager($auth, $model, $redirector, $session, $identities);

        $result = $oauth->authorize('missingProvider');
    }
}
