<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\OAuthManager;
use AdamWathan\EloquentOAuth\Providers\Provider as AbstractProvider;

class ProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_can_get_authorize_url()
    {
        $redirectUri = 'http://myapp.dev/provider/login';
        $config = array(
            'id' => '1',
            'secret' => 'foobar',
            'redirect' => $redirectUri,
            );
        $httpClient = M::mock('GuzzleHttp\\Client')->shouldIgnoreMissing();
        $input = M::mock('Illuminate\\Http\\Request')->shouldIgnoreMissing();

        $provider = new Provider($config, $httpClient, $input);

        $state = 'baz';
        $expected = 'http://example.com/authorize?client_id=1&scope=email&redirect_uri=http://myapp.dev/provider/login&response_type=code&state='.$state;
        $result = $provider->authorizeUrl($state);
        $this->assertEquals($expected, $result);
    }

    public function test_can_get_user_details()
    {
        $redirectUri = 'http://myapp.dev/provider/login';
        $config = array(
            'id' => '1',
            'secret' => 'foobar',
            'redirect' => $redirectUri,
            );
        $httpClient = M::mock('GuzzleHttp\\Client')->shouldIgnoreMissing();
        $input = M::mock('Illuminate\\Http\\Request')->shouldIgnoreMissing();

        $provider = new Provider($config, $httpClient, $input);

        $httpClient->shouldReceive('post->getBody')->andReturn('abc123');
        $httpClient->shouldReceive('get->getBody')->andReturn('{"user_id":"1","nick_name":"john.doe","first_name":"John","last_name":"Doe","email":"john.doe@example.com","photo":"http:\/\/example.com\/photos\/john_doe.jpg"}');
        $input->shouldReceive('has')->andReturn(true);
        $input->shouldReceive('get')->andReturn('authorization-code');

        $details = $provider->getUserDetails();
        $this->assertInstanceOf('AdamWathan\\EloquentOAuth\\ProviderUserDetails', $details);
        $this->assertEquals('abc123', $details->accessToken);
        $this->assertEquals('john.doe', $details->nickname);
        $this->assertEquals('John', $details->firstName);
        $this->assertEquals('Doe', $details->lastName);
        $this->assertEquals('john.doe@example.com', $details->email);
        $this->assertEquals('http://example.com/photos/john_doe.jpg', $details->imageUrl);
    }

    /**
     * @expectedException AdamWathan\EloquentOAuth\Exceptions\ApplicationRejectedException
     */
    public function test_get_user_details_throws_exception_if_user_rejects_application()
    {
        $redirectUri = 'http://myapp.dev/provider/login';
        $config = array(
            'id' => '1',
            'secret' => 'foobar',
            'redirect' => $redirectUri,
            );
        $httpClient = M::mock('GuzzleHttp\\Client')->shouldIgnoreMissing();
        $input = M::mock('Illuminate\\Http\\Request')->shouldIgnoreMissing();

        $provider = new Provider($config, $httpClient, $input);

        $httpClient->shouldReceive('post->getBody')->andReturn('abc123');
        $httpClient->shouldReceive('get->getBody')->andReturn('{"user_id":"1","nick_name":"john.doe","first_name":"John","last_name":"Doe","email":"john.doe@example.com","photo":"http:\/\/example.com\/photos\/john_doe.jpg"}');
        $input->shouldReceive('has')->andReturn(false);

        $details = $provider->getUserDetails();
    }
}

class Provider extends AbstractProvider
{
    protected $scope = array(
        'email',
        );
    protected function getAuthorizeUrl()
    {
        return 'http://example.com/authorize';
    }

    protected function getAccessTokenBaseUrl()
    {
        return 'http://example.com/access-token';
    }

    protected function getUserDataUrl()
    {
        return 'http://api.example.com/user-details';
    }

    protected function parseTokenResponse($response)
    {
        return $response;
    }

    protected function parseUserDataResponse($response)
    {
        return json_decode($response, true);
    }

    protected function userId()
    {
        return $this->getProviderUserData('user_id');
    }

    protected function nickname()
    {
        return $this->getProviderUserData('nick_name');
    }

    protected function firstName()
    {
        return $this->getProviderUserData('first_name');
    }

    protected function lastName()
    {
        return $this->getProviderUserData('last_name');
    }

    protected function email()
    {
        return $this->getProviderUserData('email');
    }

    protected function imageUrl()
    {
        return $this->getProviderUserData('photo');
    }
}
