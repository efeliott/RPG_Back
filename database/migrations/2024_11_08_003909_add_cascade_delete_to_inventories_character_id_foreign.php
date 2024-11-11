<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeDeleteToInventoriesCharacterIdForeign extends Migration
{
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['character_id']);
            $table->foreign('character_id')
                  ->references('character_id')->on('characters')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropForeign(['character_id']);
            $table->foreign('character_id')
                  ->references('character_id')->on('characters')
                  ->onDelete('restrict');
        });
    }
}
