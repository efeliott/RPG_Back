<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Item;
use App\Models\Character;

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
      
}
