<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_shops_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id('shop_id');
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')
                    ->references('session_id')
                    ->on('sessions')
                    ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shops');
    }
}
