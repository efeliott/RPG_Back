<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quest extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'quests';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'is_finished',
        'session_id'
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
        'is_finished' => 'boolean'
    ];

    /**
     * Obtient la session associée à cette quête.
     */
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'session_id');
    }
}
