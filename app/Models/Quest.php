<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    use HasFactory;

    protected $primaryKey = 'quest_id';

    protected $fillable = [
        'title', 'description', 'is_finished', 'session_id'
    ];

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
}
