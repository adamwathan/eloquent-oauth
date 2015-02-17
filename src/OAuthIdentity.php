<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Config;

/**
 * @property $id
 * @property $user_id
 * @property $provider
 * @property $provider_user_id
 * @property $access_token
 */
class OAuthIdentity extends Eloquent
{
    protected static $configuredTable = 'oauth_identities';

    public static function configureTable($table)
    {
        static::$configuredTable = $table;
    }

    public function getTable()
    {
        return static::$configuredTable;
    }
}
