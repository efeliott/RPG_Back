<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_quests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestsTable extends Migration
{
    public function up()
    {
        Schema::create('quests', function (Blueprint $table) {
            $table->id('quest_id');
            $table->string('title');
            $table->text('description');
            $table->boolean('is_finished');
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('session_id')->on('sessions');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quests');
    }
}

