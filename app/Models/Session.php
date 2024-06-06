<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $primaryKey = 'session_id';

    protected $fillable = [
        'title', 'description', 'game_master_id', 'token',
    ];

    // Définir la relation many-to-many avec le modèle User en utilisant le modèle pivot
    public function players()
    {
        return $this->hasMany(Player::class, 'session_id', 'session_id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'session_id',)
                    ->withTimestamps('created_at', 'updated_at');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'session_user', 'session_id', 'user_id');
    }

    public function quests()
    {
        return $this->hasMany(Quest::class, 'session_id', 'session_id');
    }
}
