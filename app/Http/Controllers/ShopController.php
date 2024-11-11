<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ShopItem;
use App\Models\Item;

class ShopController extends Controller
{
    // Récupère tous les items du shop pour une session
    public function getShopItems($sessionId)
    {
        $shop = Shop::firstOrCreate(['session_id' => $sessionId]);
        $shopItems = ShopItem::where('shop_id', $shop->shop_id)
                            ->with('item')
                            ->get();

        return response()->json($shopItems);
    }

    // Ajoute un nouvel item au shop avec un prix spécifique
    public function addItemToShop(Request $request, $sessionId)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'img_url' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        // Vérifie ou crée le shop pour la session
        $shop = Shop::firstOrCreate(['session_id' => $sessionId]);

        // Crée l'item
        $item = Item::create([
            'title' => $request->title,
            'description' => $request->description,
            'img_url' => $request->img_url,
        ]);

        // Ajoute l'item au shop avec le prix défini
        ShopItem::create([
            'shop_id' => $shop->shop_id,
            'item_id' => $item->item_id,
            'price' => $request->price,
        ]);

        return response()->json(['message' => 'Item added to shop successfully.']);
    }

    // Supprime un item du shop
    public function deleteItemFromShop($sessionId, $itemId)
    {
        $shop = Shop::where('session_id', $sessionId)->first();

        if ($shop) {
            ShopItem::where('shop_id', $shop->shop_id)
                    ->where('item_id', $itemId)
                    ->delete();
            return response()->json(['message' => 'Item removed from shop successfully.']);
        }

        return response()->json(['message' => 'Shop or item not found.'], 404);
    }

    // Met à jour un item dans le shop
    public function updateItemInShop(Request $request, $sessionId, $itemId)
    {
        $shop = Shop::where('session_id', $sessionId)->first();
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'img_url' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        if ($shop) {
            // Mise à jour de l'item
            Item::where('item_id', $itemId)->update([
                'title' => $request->title,
                'description' => $request->description,
                'img_url' => $request->img_url,
            ]);

            // Mise à jour du prix dans ShopItem
            ShopItem::where('shop_id', $shop->shop_id)
                    ->where('item_id', $itemId)
                    ->update(['price' => $request->price]);

            return response()->json(['message' => 'Item updated successfully.']);
        }

        return response()->json(['message' => 'Shop or item not found.'], 404);
    }
}
