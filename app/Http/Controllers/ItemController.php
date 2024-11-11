<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Inventory;

class ItemController extends Controller
{
    public function createItemWithoutShop(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'img_url' => 'nullable|string',
        ]);

        $item = Item::create([
            'title' => $request->title,
            'description' => $request->description,
            'img_url' => $request->img_url,
        ]);

        return response()->json($item);
    }

    // Dans CharacterController ou InventoryController
    public function addItemToInventory(Request $request, $characterId)
    {
        $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'max_quantity' => 'required|integer|min:1',
        ]);

        $existingInventory = Inventory::where('character_id', $characterId)
                                    ->where('item_id', $request->item_id)
                                    ->first();

        if ($existingInventory) {
            $existingInventory->max_quantity += $request->max_quantity;
            $existingInventory->save();
        } else {
            Inventory::create([
                'character_id' => $characterId,
                'item_id' => $request->item_id,
                'max_quantity' => $request->max_quantity,
            ]);
        }

        return response()->json(['message' => 'Item ajouté à l\'inventaire'], 201);
    }
}
