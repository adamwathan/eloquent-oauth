<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Config;

class OAuthIdentity extends Eloquent
{
	protected $table = 'oauth_identities';
	public function getTable()
	{
		return Config::get('eloquent-oauth::table');
	}

	public static function baz()
	{
		var_dump('abc');
	}
}
