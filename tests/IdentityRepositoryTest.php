<?php

use Patchwork\Patchwork;
use AdamWathan\EloquentOAuth\OAuthIdentity;
use AdamWathan\EloquentOAuth\IdentityRepository;
use Mockery as M;

/**
 * Honestly this test is pretty pointless and isn't really testing anything.
 * This would make much more sense as a functional test with a real database.
 */
class IdentityRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Patchwork::start();
    }

    public function test_flush()
    {
        $query = M::mock();
        $query->shouldReceive('where->where->delete');
        Patchwork::replace('AdamWathan\\EloquentOAuth\\OAuthIdentity::newQuery', function() use ($query) {
            return $query;
        });
        $user = M::mock('stdClass');
        $user->shouldReceive('getKey');
        $repo = new IdentityRepository;
        $repo->flush($user, 'provider');
    }

    public function test_getByProvider()
    {
        $identity = M::mock('AdamWathan\EloquentOAuth\OAuthIdentity');
        $query = M::mock();
        $query->shouldReceive('where->where->first')->andReturn($identity);
        Patchwork::replace('AdamWathan\\EloquentOAuth\\OAuthIdentity::newQuery', function() use ($query) {
            return $query;
        });
        $repo = new IdentityRepository;
        $result = $repo->getByProvider('provider', 1);
        $this->assertEquals($identity, $result);
    }

    public function test_store()
    {
        $identity = M::mock('AdamWathan\EloquentOAuth\OAuthIdentity');
        $identity->shouldReceive('save')->once();
        $repo = new IdentityRepository;
        $result = $repo->store($identity);
    }
}
