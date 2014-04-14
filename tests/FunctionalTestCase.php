<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class FunctionalTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->configureDatabase();
        $this->migrateIdentitiesTable();
    }

    protected function configureDatabase()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'sqlite',
            'host'      => 'localhost',
            'database'  => ':memory:',
            'username'  => 'root',
            'password'  => 'root',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            ]);
        $capsule->bootEloquent();
        $capsule->setAsGlobal();
    }

    public function migrateIdentitiesTable()
    {
        Capsule::schema()->create('oauth_identities', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('provider_user_id');
            $table->string('provider');
            $table->string('access_token');
            $table->timestamps();
        });
    }
}
