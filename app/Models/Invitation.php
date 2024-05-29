<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'email', 'token', 'accepted',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }
}
