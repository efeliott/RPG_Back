<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->bigInteger('session_id')->unsigned()->after('character_id');
            $table->bigInteger('user_id')->unsigned()->after('session_id');
            $table->foreign('session_id')->references('session_id')->on('sessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['session_id', 'user_id']);
        });
    }

};
