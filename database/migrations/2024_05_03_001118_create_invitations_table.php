<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_invitations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                    ->constrained('sessions', 'session_id')  // Référence la colonne session_id
                    ->onDelete('cascade');
            $table->string('email');
            $table->string('token');  // Token d'invitation
            $table->boolean('accepted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitations');
    }
}
