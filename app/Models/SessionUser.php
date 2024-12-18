<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionUser extends Model
{
    use HasFactory;

    protected $table = 'session_user';

    protected $fillable = [
        'session_id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;
}
