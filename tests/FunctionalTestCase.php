<?php

use Illuminate\Database\Capsule\Manager as DB;

abstract class FunctionalTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->configureDatabase();
        $this->migrateIdentitiesTable();
    }

    protected function configureDatabase()
    {
        $db = new DB;
        $db->addConnection(array(
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ));
        $db->bootEloquent();
        $db->setAsGlobal();
    }

    public function migrateIdentitiesTable()
    {
        DB::schema()->create('oauth_identities', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('provider_user_id');
            $table->string('provider');
            $table->string('access_token');
            $table->timestamps();
        });
    }
}
