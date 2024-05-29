<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'player_id',
        'amount',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
