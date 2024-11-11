<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'inventories';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'character_id',
        'item_id',
        'max_quantity'
    ];

    /**
     * Obtient le personnage associé à cet inventaire.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'character_id', 'character_id');
    }

    /**
     * Obtient l'item associé à cet inventaire.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
