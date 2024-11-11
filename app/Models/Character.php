<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Character extends Model
{
    use HasFactory;

    /**
     * Le nom de la clé primaire personnalisée du modèle.
     *
     * @var string
     */
    protected $primaryKey = 'character_id';

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
    protected $fillable = ['name', 'class', 'abilities', 'session_id', 'user_id'];

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

    /**
     * Obtenir la session à laquelle appartient le personnage.
     */
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    /**
     * Obtenir l'utilisateur propriétaire du personnage.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
