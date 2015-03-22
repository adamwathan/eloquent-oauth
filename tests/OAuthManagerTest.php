<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\OAuthManager;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\RouteCollection;
use Illuminate\Http\Request;

class OAuthManagerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_it_returns_a_redirect_to_the_authorize_url()
    {
        $redirector = $this->buildRedirector();
        $authenticator = M::mock('AdamWathan\EloquentOAuth\Authenticator');
        $socialnorm = M::mock('SocialNorm\SocialNorm');
        $socialnorm->shouldReceive('authorize')->with('example')->andReturn('http://example.com/authorize');

        $oauth = new OAuthManager($redirector, $authenticator, $socialnorm);
        $response = $oauth->authorize('example');
        $this->assertEquals('http://example.com/authorize', $response->getTargetUrl());
    }

    public function test_it_logs_the_user_in()
    {
        $providerAlias = 'twitbook';
        $socialnormUser = new SocialNorm\User([]);
        $callback = function () {};

        $redirector = $this->buildRedirector();

        $authenticator = M::spy('AdamWathan\EloquentOAuth\Authenticator');

        $socialnorm = M::mock('SocialNorm\SocialNorm');
        $socialnorm->shouldReceive('getUser')
            ->with($providerAlias)
            ->andReturn($socialnormUser);

        $oauth = new OAuthManager($redirector, $authenticator, $socialnorm);
        $oauth->login($providerAlias, $callback);

        $authenticator->shouldHaveReceived('login')->with($providerAlias, $socialnormUser, $callback);
    }

    private function buildRedirector()
    {
        return new Redirector(new UrlGenerator(new RouteCollection, new Request));
    }
}
