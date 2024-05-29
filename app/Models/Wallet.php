<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'player_id',
        'balance',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
