<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_character_id_to_wallets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCharacterIdToWalletsTable extends Migration
{
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->bigInteger('character_id')->unsigned()->nullable();
            $table->foreign('character_id')->references('character_id')->on('characters')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['character_id']);
            $table->dropColumn('character_id');
        });
    }
}
