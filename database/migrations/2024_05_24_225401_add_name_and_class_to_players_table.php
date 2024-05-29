<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_name_and_class_to_players_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameAndClassToPlayersTable extends Migration
{
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->string('name')->after('session_id');
            $table->string('class')->after('name');
        });
    }

    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('class');
        });
    }
}
