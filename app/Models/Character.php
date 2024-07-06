<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Character extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'characters';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'class',
        'abilities'
    ];

    /**
     * Les attributs qui devraient être masqués pour les tableaux.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Les attributs qui devraient être castés en types natifs.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'array', // Cast en array si stocké comme JSON.
    ];
}
