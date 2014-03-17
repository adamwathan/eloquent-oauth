<?php namespace AdamWathan\EloquentOAuth\Providers;

interface ProviderInterface
{
	public function authorizeUrl();
	public function getUserDetails();
}
