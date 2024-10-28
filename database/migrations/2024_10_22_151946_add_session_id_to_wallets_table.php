<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionIdToWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Ajout de la colonne session_id et de la clé étrangère
            $table->unsignedBigInteger('session_id')->after('user_id')->nullable();
            $table->foreign('session_id')->references('session_id')->on('sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            // Suppression de la colonne et de la clé étrangère en cas de rollback
            $table->dropForeign(['session_id']);
            $table->dropColumn('session_id');
        });
    }
}
