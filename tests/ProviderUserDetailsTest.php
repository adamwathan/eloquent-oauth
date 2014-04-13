<?php

use AdamWathan\EloquentOAuth\ProviderUserDetails;

class ProviderUserDetailsTest extends PHPUnit_Framework_TestCase
{
    public function test_can_set_all_properties_and_retrieve()
    {
        $details = new ProviderUserDetails(array(
            'accessToken' => 'abc123',
            'userId' => 'foobar',
            'nickname' => 'john.doe',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'imageUrl' => 'http://example.com/john-doe.jpg',
            ));
        $this->assertEquals('abc123', $details->accessToken);
        $this->assertEquals('foobar', $details->userId);
        $this->assertEquals('john.doe', $details->nickname);
        $this->assertEquals('John', $details->firstName);
        $this->assertEquals('Doe', $details->lastName);
        $this->assertEquals('john.doe@example.com', $details->email);
        $this->assertEquals('http://example.com/john-doe.jpg', $details->imageUrl);
    }

    public function test_properties_not_set_return_null()
    {
        $details = new ProviderUserDetails(array(
            'accessToken' => 'abc123',
            ));
        $this->assertNull($details->userId);
    }
}
