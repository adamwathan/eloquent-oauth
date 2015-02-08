<?php namespace AdamWathan\EloquentOAuth;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Config;

/**
 * @property integer $user_id
 * @property string $provider_user_id
 * @property string $provider
 * @property string $access_token
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
