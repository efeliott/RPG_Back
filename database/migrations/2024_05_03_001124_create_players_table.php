<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_players_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayersTable extends Migration
{
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id(); // Primary key 'id'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key 'user_id' referring to 'id' in 'users' table
            $table->foreignId('session_id')->constrained('sessions', 'session_id')->onDelete('cascade'); // Foreign key 'session_id' referring to 'session_id' in 'sessions' table
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('players');
    }
}
