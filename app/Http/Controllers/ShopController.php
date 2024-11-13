<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ShopItem;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use App\Models\Character;

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

    public function purchaseItem($sessionId, $itemId, $characterId)
    {
        // Récupérer le personnage pour vérifier qu'il appartient bien à l'utilisateur et à la session
        $user = Auth::user();
        $character = Character::where('character_id', $characterId)
                              ->where('user_id', $user->id)
                              ->where('session_id', $sessionId)
                              ->first();
    
        if (!$character) {
            return response()->json(['message' => 'Character not found or does not belong to this session'], 404);
        }
    
        // Récupérer l'item dans le shop de la session
        $shopItem = ShopItem::where('shop_id', $sessionId)
                            ->where('item_id', $itemId)
                            ->first();
    
        if (!$shopItem) {
            return response()->json(['message' => 'Item not found in shop for this session'], 404);
        }
    
        // Vérifier que le personnage a un portefeuille avec suffisamment de fonds
        $wallet = Wallet::where('character_id', $characterId)->first();
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found for this character'], 404);
        }
    
        if ($wallet->balance < $shopItem->price) {
            return response()->json(['message' => 'Insufficient funds in wallet'], 400);
        }
    
        // Déduire le prix de l'item du portefeuille
        $wallet->balance -= $shopItem->price;
        $wallet->save();
    
        // Ajouter l'item à l'inventaire
        $inventoryItem = Inventory::where('character_id', $characterId)
                                  ->where('item_id', $itemId)
                                  ->first();
    
        if ($inventoryItem) {
            // Si l'item est déjà dans l'inventaire, augmentez la quantité
            $inventoryItem->max_quantity += 1;
        } else {
            // Sinon, créez une nouvelle entrée dans l'inventaire
            $inventoryItem = new Inventory();
            $inventoryItem->character_id = $characterId;
            $inventoryItem->item_id = $itemId;
            $inventoryItem->max_quantity = 1;
        }
        $inventoryItem->save();
    
        // Réponse avec les détails de l'achat
        return response()->json([
            'message' => 'Item purchased successfully',
            'item' => [
                'item_id' => $shopItem->item_id,
                'title' => $shopItem->item->title,
                'price' => $shopItem->price,
            ],
            'wallet' => [
                'balance' => $wallet->balance
            ]
        ], 200);
    }
}
