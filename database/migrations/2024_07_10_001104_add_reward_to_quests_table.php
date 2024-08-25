<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRewardToQuestsTable extends Migration
{
    public function up()
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->integer('reward')->default(0); // Ajoute la colonne reward avec une valeur par défaut de 0
        });
    }

    public function down()
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('reward');
        });
    }
}
