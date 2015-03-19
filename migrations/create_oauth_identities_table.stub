<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauthIdentitiesTable extends Migration
{
    public function up()
    {
        $tableName = Config::get('eloquent-oauth.table');
        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('provider_user_id');
            $table->string('provider');
            $table->string('access_token');
            $table->timestamps();
        });
    }

    public function down()
    {
        $tableName = Config::get('eloquent-oauth.table');
        Schema::drop($tableName);
    }
}
