<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_default_balance_to_wallets.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultBalanceToWallets extends Migration
{
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance', 10, 2)->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('balance', 10, 2)->change();
        });
    }
}
