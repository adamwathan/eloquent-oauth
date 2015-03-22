<?php

use AdamWathan\EloquentOAuth\OAuthIdentity;
use AdamWathan\EloquentOAuth\IdentityStore;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Mockery as M;
use SocialNorm\User as UserDetails;

class IdentityStoreTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        Eloquent::unguard();
    }

    public function test_get_by_provider()
    {
        OAuthIdentity::create(array(
            'user_id' => 1,
            'provider' => 'facebook',
            'provider_user_id' => 'foobar',
            'access_token' => 'abc123',
            ));
        OAuthIdentity::create(array(
            'user_id' => 2,
            'provider' => 'facebook',
            'provider_user_id' => 'bazfoo',
            'access_token' => 'def456',
            ));
        $details = new UserDetails(array(
            'access_token' => 'new-token',
            'id' => 'bazfoo',
            'nickname' => 'john.doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'avatar' => 'http://example.com/photos/john_doe.jpg',
        ));
        $identities = new IdentityStore;
        $identity = $identities->getByProvider('facebook', $details);
        $this->assertEquals(2, $identity->user_id);
        $this->assertEquals('facebook', $identity->provider);
        $this->assertEquals('bazfoo', $identity->provider_user_id);
        $this->assertEquals('def456', $identity->access_token);
    }

    public function test_get_by_provider_when_no_match()
    {
        OAuthIdentity::create(array(
            'user_id' => 1,
            'provider' => 'facebook',
            'provider_user_id' => 'foobar',
            'access_token' => 'abc123',
            ));
        OAuthIdentity::create(array(
            'user_id' => 2,
            'provider' => 'facebook',
            'provider_user_id' => 'bazfoo',
            'access_token' => 'def456',
            ));
        $details = new UserDetails(array(
            'access_token' => 'new-token',
            'id' => 'missing-id',
            'nickname' => 'john.doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'avatar' => 'http://example.com/photos/john_doe.jpg',
        ));
        $identities = new IdentityStore;
        $identity = $identities->getByProvider('facebook', $details);
        $this->assertNull($identity);
    }

    public function test_flush()
    {
        OAuthIdentity::create(array(
            'user_id' => 1,
            'provider' => 'facebook',
            'provider_user_id' => 'foobar',
            'access_token' => 'abc123',
            ));
        OAuthIdentity::create(array(
            'user_id' => 2,
            'provider' => 'facebook',
            'provider_user_id' => 'bazfoo',
            'access_token' => 'def456',
            ));

        $this->assertEquals(1, OAuthIdentity::where('provider', 'facebook')->where('user_id', 2)->count());

        $identities = new IdentityStore;
        $user = M::mock();
        $user->shouldReceive('getKey')->andReturn(2);
        $identities->flush($user, 'facebook');

        $this->assertEquals(0, OAuthIdentity::where('provider', 'facebook')->where('user_id', 2)->count());
    }

    public function test_store()
    {
        $identity = new OAuthIdentity(array(
            'user_id' => 1,
            'provider' => 'facebook',
            'provider_user_id' => 'foobar',
            'access_token' => 'abc123',
            ));

        $this->assertEquals(0, OAuthIdentity::count());

        $identities = new IdentityStore;
        $identities->store($identity);

        $this->assertEquals(1, OAuthIdentity::count());
    }

    public function test_user_exists_returns_true_when_user_exists()
    {
        OAuthIdentity::create(array(
            'user_id' => 2,
            'provider' => 'facebook',
            'provider_user_id' => 'bazfoo',
            'access_token' => 'def456',
            ));
        $details = new UserDetails(array(
            'access_token' => 'new-token',
            'id' => 'bazfoo',
            'nickname' => 'john.doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'avatar' => 'http://example.com/photos/john_doe.jpg',
        ));
        $identities = new IdentityStore;
        $this->assertTrue($identities->userExists('facebook', $details));
    }

    public function test_user_exists_returns_false_when_user_doesnt_exist()
    {
        OAuthIdentity::create(array(
            'user_id' => 2,
            'provider' => 'facebook',
            'provider_user_id' => 'foobar',
            'access_token' => 'def456',
            ));
        $details = new UserDetails(array(
            'access_token' => 'new-token',
            'id' => 'bazfoo',
            'nickname' => 'john.doe',
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'avatar' => 'http://example.com/photos/john_doe.jpg',
        ));
        $identities = new IdentityStore;
        $this->assertFalse($identities->userExists('facebook', $details));
    }
}
