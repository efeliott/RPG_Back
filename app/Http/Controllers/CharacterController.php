<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CharacterController extends Controller
{
    /**
     * Affiche la liste de tous les personnages.
     */
    public function index()
    {
        $characters = Character::all();
        return response()->json($characters);
    }

    /**
     * Affiche un personnage spécifique.
     */
    public function show($id)
    {
        $character = Character::find($id);
        if (!$character) {
            return response()->json(['message' => 'Character not found'], 404);
        }
        return response()->json($character);
    }

    /**
     * Crée un nouveau personnage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'abilities' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $character = Character::create($request->all());
        return response()->json($character, 201);
    }

    /**
     * Met à jour un personnage existant.
     */
    public function update(Request $request, $id)
    {
        $character = Character::find($id);
        if (!$character) {
            return response()->json(['message' => 'Character not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'class' => 'string|max:255',
            'abilities' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $character->update($request->all());
        return response()->json($character);
    }

    /**
     * Supprime un personnage.
     */
    public function destroy($id)
    {
        $character = Character::find($id);
        if (!$character) {
            return response()->json(['message' => 'Character not found'], 404);
        }
        $character->delete();
        return response()->json(['message' => 'Character deleted successfully']);
    }
}
