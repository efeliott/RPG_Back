<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ShopItem;
use App\Models\Session;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopItemController extends Controller
{
    /**
     * Affiche la liste de tous les items de la boutique.
     */
    public function index($token)
    {
        $session = Session::where('token', $token)->firstOrFail();
        $shop = Shop::where('session_id', $session->session_id)->firstOrFail();
        $items = ShopItem::where('shop_id', $shop->shop_id)->with('item')->get();
        return response()->json($items);
    }    

    /**
     * Affiche un item spécifique de la boutique.
     */
    public function show($id)
    {
        $shopItem = ShopItem::with('item')->find($id);
        if (!$shopItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        return response()->json($shopItem);
    }

    /**
     * Crée un nouvel item dans la boutique.
     */
    public function store(Request $request, $token)
    {
        $session = Session::where('token', $token)->firstOrFail();
        $shop = Shop::where('session_id', $session->session_id)->firstOrFail();
    
        if (!$shop) {
            Log::error('Shop not found for session:', ['session_id' => $session->session_id]);
            return response()->json(['message' => 'Shop not found for this session'], 404);
        }
    
        Log::info('Received request data:', $request->all());
    
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json($validator->errors(), 400);
        }
    
        // Créer l'item
        $item = Item::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);
    
        Log::info('Created item:', $item->toArray());
    
        // Associer l'item au shop avec le prix
        $shopItem = ShopItem::create([
            'shop_id' => $shop->shop_id,
            'item_id' => $item->item_id,
            'price' => $request->price,
        ]);
    
        Log::info('Created shop item:', $shopItem->toArray());
    
        return response()->json(['message' => 'Item added successfully!', 'item' => $shopItem], 201);
    }
    

    /**
     * Met à jour un item existant dans la boutique.
     */
    public function update(Request $request, $id)
    {
        $shopItem = ShopItem::find($id);
        if (!$shopItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        Log::info('Received update data:', $request->all());

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'integer',
            'shop_id' => 'exists:shops,shop_id'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json($validator->errors(), 400);
        }

        $shopItem->item->update($request->only('title', 'description'));
        $shopItem->update($request->only('price'));

        Log::info('Updated shop item:', $shopItem->toArray());

        return response()->json($shopItem);
    }

    /**
     * Supprime un item de la boutique.
     */
    public function destroy($id)
    {
        $shopItem = ShopItem::findOrFail($id);
        $shopItem->delete();

        return response()->json(['message' => 'Item removed successfully!']);
    }

    public function purchase(Request $request, $token, $itemId)
    {
        $session = Session::where('token', $token)->firstOrFail();
        $user = auth()->user();
        $wallet = Wallet::where('user_id', $user->id)
                        ->where('session_id', $session->id)
                        ->firstOrFail(); // Trouver le wallet de l'utilisateur pour cette session
        $shopItem = ShopItem::where('id', $itemId)->where('shop_id', $session->shop->id)->firstOrFail();

        if ($wallet->balance < $shopItem->price) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $wallet->balance -= $shopItem->price;
        $wallet->save();

        // Logique pour transférer l'objet à l'utilisateur, etc.

        $shopItem->delete(); // Suppression de l'objet du shop

        return response()->json(['message' => 'Item purchased successfully!', 'balance' => $wallet->balance]);
    }

    /**
     * Affiche le solde du wallet pour une session donnée.
     */
    public function getBalance($token)
    {
        $session = Session::where('token', $token)->firstOrFail();
        $user = auth()->user();
        $wallet = Wallet::where('user_id', $user->id)
                        ->where('session_id', $session->id)
                        ->firstOrFail();

        return response()->json(['balance' => $wallet->balance]);
    }
}
