<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\OAuthManager;
use Illuminate\Http\RedirectResponse;

class OAuthManagerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_it_returns_a_redirect_to_the_authorize_url()
    {
        $redirector = new SimpleRedirector;
        $authenticator = M::mock('AdamWathan\EloquentOAuth\Authenticator');
        $socialnorm = M::mock('SocialNorm\SocialNorm');
        $socialnorm->shouldReceive('authorize')->with('example')->andReturn('http://example.com/authorize');

        $oauth = new OAuthManager($redirector, $authenticator, $socialnorm);
        $response = $oauth->authorize('example');
        $this->assertEquals('http://example.com/authorize', $response->getTargetUrl());
    }
}

class SimpleRedirector
{
    public function to($path)
    {
        return new RedirectResponse($path);
    }
}
