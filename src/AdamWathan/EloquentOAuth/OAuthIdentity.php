<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Database\Eloquent\Model as Eloquent;

class OAuthIdentity extends Eloquent
{
	protected $table = 'oauth_identities';
}
