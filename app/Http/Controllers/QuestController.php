<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Session;

class QuestController extends Controller
{
    /**
     * Affiche la liste de toutes les quêtes.
     */
    public function index($token)
    {
        $session = Session::where('token', $token)->firstOrFail();
        $quests = $session->quests;
        return response()->json($quests);
    }

    /**
     * Affiche une quête spécifique.
     */
    public function show($id)
    {
        $quest = Quest::with('session')->find($id);
        if (!$quest) {
            return response()->json(['message' => 'Quest not found'], 404);
        }
        return response()->json($quest);
    }

    /**
     * Crée une nouvelle quête.
     */
    public function store(Request $request, $token)
    {
        // Récupérer la session à partir du token
        $session = Session::where('token', $token)->firstOrFail();

        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reward' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Créer et sauvegarder la nouvelle quête
        $quest = new Quest([
            'session_id' => $session->session_id, // Assurez-vous d'utiliser l'ID de la session récupérée
            'title' => $request->title,
            'description' => $request->description,
            'reward' => $request->reward,
        ]);
        $quest->save();

        return response()->json(['message' => 'Quest added successfully!', 'quest' => $quest], 201);
    }

    /**
     * Met à jour une quête existante.
     */
    public function update(Request $request, $id)
    {
        $quest = Quest::find($id);
        if (!$quest) {
            return response()->json(['message' => 'Quest not found'], 404);
        }

        $quest->update($request->all());
        return response()->json($quest);
    }

    /**
     * Met à jour le statut d'une quête.
     */
    public function updateStatus(Request $request, $id)
    {
        $quest = Quest::find($id);
        if (!$quest) {
            return response()->json(['message' => 'Quest not found'], 404);
        }

        $quest->is_finished = $request->is_finished;
        $quest->save();

        return response()->json($quest);
    }

    /**
     * Supprime une quête.
     */
    public function destroy($id)
    {
        $quest = Quest::findOrFail($id);
        $quest->delete();

        return response()->json(['message' => 'Quest removed successfully!']);
    }
}
