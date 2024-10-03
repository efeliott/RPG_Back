<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';

    protected $table = 'shopitems';

    protected $fillable = ['shop_id', 'item_id', 'price'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
