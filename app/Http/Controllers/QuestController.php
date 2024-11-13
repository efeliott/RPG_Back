<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Character;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;

class QuestController extends Controller
{
    // Récupère toutes les quêtes d'une session
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'session_id' => 'required|exists:sessions,session_id',
            'reward' => 'required|integer',
        ]);

        $quest = Quest::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'session_id' => $validated['session_id'],
            'reward' => $validated['reward'], // Supprimez le `?? 0`
            'is_finished' => false,
        ]);

        return response()->json(['message' => 'Quest created successfully', 'quest' => $quest], 201);
    }


    public function update(Request $request, $questId)
    {
        $quest = Quest::findOrFail($questId);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'reward' => 'nullable|integer',
            'is_finished' => 'boolean',
        ]);

        // Assure que 'reward' est bien défini, même si aucun n'est envoyé
        $validated['reward'] = $validated['reward'] ?? $quest->reward;

        $quest->update($validated);

        return response()->json(['message' => 'Quest updated successfully', 'quest' => $quest]);
    }


    // Supprime une quête
    public function destroy($questId)
    {
        $quest = Quest::where('quest_id', $questId)->firstOrFail();
        $quest->delete();
    
        return response()->json(['message' => 'Quest deleted successfully']);
    }    

    // Assigne la quête à un joueur
    public function assignToPlayer(Request $request, $questId)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $quest = Quest::findOrFail($questId);
        $quest->player_id = $validated['player_id'];
        $quest->save();

        return response()->json(['message' => 'Quest assigned to player', 'quest' => $quest]);
    }

    // Selection d'une quête par un joueur
    public function selectQuest($questId, $playerId)
    {
        $quest = Quest::where('quest_id', $questId)
                    ->where('player_id', null)
                    ->where('is_finished', false)
                    ->firstOrFail();

        $quest->player_id = $playerId;
        $quest->save();

        return response()->json(['message' => 'Quest selected by player', 'quest' => $quest]);
    }

    // Récupère toutes les quêtes d'une session
    public function getQuests($session_id)
    {
        $session = Session::findOrFail($session_id);
        $quests = $session->quests;

        return response()->json($quests);
    }

    public function acceptQuest($sessionId, $questId, $characterId)
    {
        $userId = Auth::id();

        // Vérifier si le personnage de l'utilisateur existe dans la session
        $character = Character::where('session_id', $sessionId)
                            ->where('user_id', $userId)
                            ->first();

        if (!$character) {
            return response()->json(['message' => 'Character not found in this session'], 404);
        }

        // Trouver la quête
        $quest = Quest::where('quest_id', $questId)
                    ->where('session_id', $sessionId)
                    ->first();

        if (!$quest) {
            return response()->json(['message' => 'Quest not found'], 404);
        }

        // Assigner la quête au personnage
        $quest->character_id = $characterId;
        $quest->save();

        return response()->json(['message' => 'Quest accepted successfully', 'quest' => $quest]);
    }

    public function markAsComplete($questId)
    {
        // Récupérer la quête avec le character_id associé
        $quest = Quest::where('quest_id', $questId)->where('is_finished', false)->first();

        // Vérifier si la quête est valide et non terminée
        if (!$quest || !$quest->character_id) {
            return response()->json(['message' => 'Quête non trouvée ou déjà terminée.'], 404);
        }

        // Utiliser le character_id de la quête pour identifier le personnage
        $characterId = $quest->character_id;

        // Débuter une transaction pour assurer la mise à jour des données
        DB::beginTransaction();

        try {
            // Récupérer ou créer le wallet du personnage
            $wallet = Wallet::firstOrCreate(
                ['character_id' => $characterId],
                ['balance' => 0]
            );

            // Ajouter la récompense de la quête au solde du wallet
            $wallet->balance += $quest->reward;
            $wallet->save();

            // Marquer la quête comme terminée
            $quest->is_finished = true;
            $quest->save();

            // Valider la transaction
            DB::commit();

            return response()->json(['message' => 'Quête terminée et récompense ajoutée au porte-monnaie.'], 200);
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la finalisation de la quête.'], 500);
        }
    }
}
