<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    /**
     * Affiche la liste de tous les magasins.
     */
    public function index()
    {
        $shops = Shop::with(['session', 'items'])->get();
        return response()->json($shops);
    }

    /**
     * Affiche un magasin spécifique.
     */
    public function show($id)
    {
        $shop = Shop::with(['session', 'items'])->find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        return response()->json($shop);
    }

    /**
     * Crée un nouveau magasin.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,session_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $shop = Shop::create($request->all());
        return response()->json($shop, 201);
    }

    /**
     * Met à jour un magasin existant.
     */
    public function update(Request $request, $id)
    {
        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'session_id' => 'exists:sessions,session_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $shop->update($request->only(['session_id']));
        return response()->json($shop);
    }

    /**
     * Supprime un magasin.
     */
    public function destroy($id)
    {
        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        $shop->delete();
        return response()->json(['message' => 'Shop deleted successfully']);
    }
}
