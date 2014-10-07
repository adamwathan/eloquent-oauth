<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\Authorizer;

class AuthorizerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_placeholder() {}
}
