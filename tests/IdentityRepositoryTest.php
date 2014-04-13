<?php

use Patchwork\Patchwork;
use AdamWathan\EloquentOAuth\OAuthIdentity;

class IdentityRepository extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Patchwork::start();
    }

    public function testBlah()
    {
        Patchwork::replace('AdamWathan\\EloquentOAuth\\OAuthIdentity::baz', function() {
            var_dump('def');
        });
        OAuthIdentity::baz();
    }
}
