<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_inventories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->unsignedBigInteger('character_id');
            $table->unsignedBigInteger('item_id');
            $table->integer('max_quantity');
            $table->foreign('character_id')->references('character_id')->on('characters');
            $table->foreign('item_id')->references('item_id')->on('items');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
