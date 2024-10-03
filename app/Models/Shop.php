<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shop extends Model
{
    use HasFactory;

    protected $primaryKey = 'shop_id';

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'shops';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'session_id'
    ];

    /**
     * Obtient la session associée à ce magasin.
     */
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'session_id');
    }

    /**
     * Obtient les articles vendus dans ce magasin.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'shopitems', 'shop_id', 'item_id')
                    ->withPivot('price');
    }
}
