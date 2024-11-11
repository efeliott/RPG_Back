<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->unsignedBigInteger('player_id')->nullable()->after('reward')->index()->comment('ID of the player assigned to the quest');
            $table->foreign('player_id')->references('id')->on('players')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropColumn('player_id');
        });
    }
};
