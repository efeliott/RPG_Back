<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestController extends Controller
{
    /**
     * Affiche la liste de toutes les quêtes.
     */
    public function index()
    {
        $quests = Quest::with('session')->get();
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_finished' => 'required|boolean',
            'session_id' => 'required|exists:sessions,session_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $quest = Quest::create($request->all());
        return response()->json($quest, 201);
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
        $quest = Quest::find($id);
        if (!$quest) {
            return response()->json(['message' => 'Quest not found'], 404);
        }
        $quest->delete();
        return response()->json(['message' => 'Quest deleted successfully']);
    }
}
