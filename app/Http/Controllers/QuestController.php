<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Session;

class QuestController extends Controller
{
    // Récupère toutes les quêtes d'une session
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'session_id' => 'required|exists:sessions,session_id',
            'reward' => 'nullable|integer',
        ]);

        $quest = Quest::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'session_id' => $validated['session_id'],
            'reward' => $validated['reward'] ?? 0,
            'is_finished' => false,
        ]);

        return response()->json(['message' => 'Quest created successfully', 'quest' => $quest]);
    }

    // Met à jour une quête
    public function update(Request $request, $questId)
    {
        $quest = Quest::findOrFail($questId);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'reward' => 'nullable|integer',
            'is_finished' => 'boolean',
        ]);

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
}
