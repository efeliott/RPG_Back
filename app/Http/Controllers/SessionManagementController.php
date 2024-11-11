<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Inventory;
use App\Models\Wallet;
use App\Models\Item;
use App\Models\Shop;
use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionManagementController extends Controller
{
    // Ajouter de l'argent à un wallet
    public function addMoneyToWallet(Request $request, $sessionId, $userId)
    {
        $wallet = Wallet::where('session_id', $sessionId)->where('user_id', $userId)->firstOrFail();
        $wallet->balance += $request->input('amount');
        $wallet->save();

        return response()->json(['message' => 'Money added successfully', 'balance' => $wallet->balance]);
    }

    // Visualiser l'inventaire d'un personnage
    public function getInventory($characterId)
    {
        $inventory = Inventory::where('character_id', $characterId)->with('item')->get();
        return response()->json($inventory);
    }

    // Gestion des wallets - voir et modifier le solde
    public function getWallets($sessionId)
    {
        $wallets = Wallet::where('session_id', $sessionId)
                        ->with([
                            'character:character_id,name',  // Utilise character_id pour la clé primaire
                            'user:id,username'  // Utilise id pour la clé primaire dans User
                        ])
                        ->get();

        log::info('Wallets retrieved:', ['wallets' => $wallets]);
        return response()->json($wallets);
    }

    public function updateWallet(Request $request, $walletId)
    {
        $wallet = Wallet::findOrFail($walletId);
        $wallet->balance += $request->input('amount');
        $wallet->save();

        return response()->json(['message' => 'Wallet updated successfully', 'balance' => $wallet->balance]);
    }

    // Gestion du shop : voir les items disponibles
    public function getShopItems($sessionId)
    {
        $shop = Shop::where('session_id', $sessionId)->firstOrFail();
        $shopItems = ShopItem::where('shop_id', $shop->shop_id)->with('item')->get();

        return response()->json($shopItems);
    }

    // Ajouter un item au shop
    public function addItemToShop(Request $request, $sessionId)
    {
        $shop = Shop::firstOrCreate(['session_id' => $sessionId]);
        $item = Item::findOrFail($request->input('item_id'));

        ShopItem::create([
            'shop_id' => $shop->shop_id,
            'item_id' => $item->item_id,
            'price' => $request->input('price'),
        ]);

        return response()->json(['message' => 'Item added to shop successfully']);
    }

    // Supprimer un item du shop
    public function removeItemFromShop($shopId, $itemId)
    {
        ShopItem::where('shop_id', $shopId)->where('item_id', $itemId)->delete();

        return response()->json(['message' => 'Item removed from shop']);
    }
}
