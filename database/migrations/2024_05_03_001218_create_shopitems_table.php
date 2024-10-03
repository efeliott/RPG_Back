<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_shopitems_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopItemsTable extends Migration
{
    public function up()
    {
        Schema::create('shopitems', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('item_id');
            $table->decimal('price', 10, 2);
            $table->primary(['shop_id', 'item_id']);
            $table->foreign('shop_id')
                    ->references('shop_id')
                    ->on('shops')
                    ->onDelete('cascade');
            $table->foreign('item_id')
                    ->references('item_id')
                    ->on('items');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopitems');
    }
}
