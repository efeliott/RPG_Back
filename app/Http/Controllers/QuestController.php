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
        $session = Session::where('token', $token)->firstOrFail();

        $quest = new Quest([
            'session_id' => $session->session_id,
            'title' => $request->title,
            'description' => $request->description,
        ]);
        $quest->save();

        return response()->json(['message' => 'Quest added successfully!', 'quest' => $quest]);
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

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string',
            'is_finished' => 'boolean',
            'session_id' => 'exists:sessions,session_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $quest->update($request->all());
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
