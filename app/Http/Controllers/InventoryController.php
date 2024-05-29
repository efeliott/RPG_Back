<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Affiche la liste de tous les inventaires.
     */
    public function index()
    {
        $inventories = Inventory::with(['character', 'item'])->get();
        return response()->json($inventories);
    }

    /**
     * Affiche un inventaire spécifique.
     */
    public function show($id)
    {
        $inventory = Inventory::with(['character', 'item'])->find($id);
        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }
        return response()->json($inventory);
    }

    /**
     * Crée un nouvel inventaire.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'character_id' => 'required|exists:characters,character_id',
            'item_id' => 'required|exists:items,item_id',
            'max_quantity' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $inventory = Inventory::create($request->all());
        return response()->json($inventory, 201);
    }

    /**
     * Met à jour un inventaire existant.
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::find($id);
        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'character_id' => 'exists:characters,character_id',
            'item_id' => 'exists:items,item_id',
            'max_quantity' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $inventory->update($request->all());
        return response()->json($inventory);
    }

    /**
     * Supprime un inventaire.
     */
    public function destroy($id)
    {
        $inventory = Inventory::find($id);
        if (!$inventory) {
            return response()->json(['message' => 'Inventory not found'], 404);
        }
        $inventory->delete();
        return response()->json(['message' => 'Inventory deleted successfully']);
    }
}
