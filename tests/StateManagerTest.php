<?php

use Mockery as M;
use AdamWathan\EloquentOAuth\StateManager;

class StateManagerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_can_verify_valid_state()
    {
        $session = M::mock('Illuminate\\Session\\Store')->shouldIgnoreMissing();
        $request = M::mock('Illuminate\\Http\\Request')->shouldIgnoreMissing();

        $stateManager = new StateManager($session, $request);

        $session->shouldReceive('get')->andReturn('abc123')->once();
        $request->shouldReceive('has')->andReturn(true)->once();
        $request->shouldReceive('get')->andReturn('abc123')->once();

        $this->assertTrue($stateManager->verifyState());
    }

    public function test_can_verify_invalid_state()
    {
        $session = M::mock('Illuminate\\Session\\Store')->shouldIgnoreMissing();
        $request = M::mock('Illuminate\\Http\\Request')->shouldIgnoreMissing();

        $stateManager = new StateManager($session, $request);

        $session->shouldReceive('get')->andReturn('abc123')->once();
        $request->shouldReceive('has')->andReturn(true)->once();
        $request->shouldReceive('get')->andReturn('123abc')->once();

        $this->assertFalse($stateManager->verifyState());
    }

    public function test_can_verify_missing_state()
    {
        $session = M::mock('Illuminate\\Session\\Store')->shouldIgnoreMissing();
        $request = M::mock('Illuminate\\Http\\Request')->shouldIgnoreMissing();

        $stateManager = new StateManager($session, $request);

        $request->shouldReceive('has')->andReturn(false)->once();

        $this->assertFalse($stateManager->verifyState());
    }
}
