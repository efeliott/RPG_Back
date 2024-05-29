<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SessionUser extends Pivot
{
    protected $table = 'session_user';

    // Ajouter les colonnes dates de création et de mise à jour
    protected $fillable = [
        'session_id',
        'user_id',
    ];

    // Indiquer que la table utilise les timestamps
    public $timestamps = true;
}
