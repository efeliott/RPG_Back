<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use App\Models\Character;
use App\Models\Shop;
use App\Models\ShopItem;

class InventoryController extends Controller
{
    public function getInventory($characterId)
    {
        // Vérifie que le personnage existe
        $character = Character::findOrFail($characterId);

        // Récupère les items de l'inventaire du personnage avec les détails des items
        $inventoryItems = Inventory::where('character_id', $characterId)
                                    ->with(['item' => function($query) {
                                        $query->select('item_id', 'title', 'description');
                                    }])
                                    ->get(['inventory_id', 'item_id', 'max_quantity', 'character_id']);

        return response()->json($inventoryItems, 200);
    }

    public function deleteInventoryItem($inventoryId)
    {
        $inventoryItem = Inventory::where('inventory_id', $inventoryId);
    
        if (!$inventoryItem->exists()) {
            return response()->json(['message' => 'Item introuvable dans l\'inventaire'], 404);
        } else {
            // Suppression de l'item trouvé
            $inventoryItem->delete();
        }
    
        return response()->json(['message' => 'Item supprimé de l\'inventaire'], 200);
    }

    // Récupération de l'inventaire d'un joueur
    public function getPlayerInventory($sessionId, $characterId)
    {
        // Vérifie si le character appartient bien à la session pour éviter des accès non autorisés
        $character = Character::where('session_id', $sessionId)->where('character_id', $characterId)->first();

        if (!$character) {
            return response()->json(['message' => 'Character not found in this session'], 404);
        }

        // Récupère les items de l'inventaire associés au character spécifié
        $inventoryItems = Inventory::where('character_id', $characterId)
                            ->with('item:item_id,title,description') // Inclut les détails de chaque item
                            ->get();

        return response()->json($inventoryItems);
    }

    // Récupération des détails d'un item sur l'inventaire
    public function getItemDetail($inventoryId)
    {
        // Récupère l'item d'inventaire spécifique avec les détails de l'item associé
        $inventoryItem = Inventory::where('inventory_id', $inventoryId)
                                    ->with(['item' => function($query) {
                                        $query->select('item_id', 'title', 'description', 'img_url');
                                    }])
                                    ->first(['inventory_id', 'item_id', 'max_quantity', 'character_id']);

        if (!$inventoryItem) {
            return response()->json(['message' => 'Item not found in inventory'], 404);
        }

        // Retourne uniquement les informations de l'item
        return response()->json([
            'title' => $inventoryItem->item->title,
            'description' => $inventoryItem->item->description,
            'img_url' => $inventoryItem->item->img_url,
            'max_quantity' => $inventoryItem->max_quantity,
        ]);
    }

}
