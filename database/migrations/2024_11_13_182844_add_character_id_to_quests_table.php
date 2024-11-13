<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('quests', function (Blueprint $table) {
            // Assurez-vous que la colonne est bien de type unsignedBigInteger
            $table->unsignedBigInteger('character_id')->nullable()->after('reward');
            
            // Ajoutez la contrainte de clé étrangère
            $table->foreign('character_id')
                  ->references('id')
                  ->on('characters')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('quests', function (Blueprint $table) {
            // Supprime la clé étrangère en cas de rollback
            $table->dropForeign(['character_id']);
            $table->dropColumn('character_id');
        });
    }
};