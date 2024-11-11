<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'game_master_id');
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    // Relation avec les personnages
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class, 'user_id', 'id');
    }
}