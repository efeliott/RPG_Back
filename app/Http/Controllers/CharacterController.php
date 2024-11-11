<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Wallet;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;

class CharacterController extends Controller
{
    // Visualiser les personnages d'une session
    public function getCharacters($sessionId)
    {
        $characters = Character::where('session_id', $sessionId)->get();
        return response()->json($characters);
    }

    // Créer un personnage
    public function createCharacter(Request $request, $sessionId)
    {
        $session_id = $sessionId;

        $request->validate([
            'name' => 'required|string',
            'class' => 'required|string',
            'abilities' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);
    
        // Vérifie si l'utilisateur a déjà un personnage dans cette session
        $existingCharacter = Character::where('session_id', $session_id)
                                      ->where('user_id', $request->user_id)
                                      ->first();
    
        if ($existingCharacter) {
            return response()->json(['message' => 'User already has a character in this session'], 400);
        }
    
        // Crée le personnage
        $character = Character::create([
            'name' => $request->name,
            'class' => $request->class,
            'abilities' => $request->abilities,
            'session_id' => $session_id,
            'user_id' => $request->user_id,
        ]);

        if ($character->character_id) {
            Log::info('Character ID generated:', ['character_id' => $character->character_id]);
        } else {
            Log::error('Failed to generate character ID');
        }
    
        // Crée un wallet pour le personnage
        Wallet::create([
            'user_id' => $request->user_id,
            'session_id' => $session_id,
            'character_id' => $character->character_id,
        ]);       
    
        return response()->json($character, 201);
    }

    // Met à jour un personnage existant
    public function updateCharacter(Request $request, $characterId)
    {
        $request->validate([
            'name' => 'required|string',
            'class' => 'required|string',
            'abilities' => 'nullable|string',
        ]);

        $character = Character::findOrFail($characterId);
        $character->update($request->all());

        return response()->json(['message' => 'Character updated successfully.']);
    }

    // Supprime un personnage
    public function deleteCharacter($characterId)
    {
        $character = Character::findOrFail($characterId);
        $character->delete();

        return response()->json(['message' => 'Character deleted successfully.']);
    }
}
