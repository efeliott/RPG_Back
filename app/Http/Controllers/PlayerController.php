<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class PlayerController extends Controller
{
    /**
     * Affiche la liste de tous les joueurs.
     */
    public function index($sessionToken)
    {
        $session = Session::where('token', $sessionToken)->firstOrFail();

        if ($session->game_master_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $players = Player::where('session_id', $session->session_id)->get();
        return response()->json($players);
    }

    /**
     * Affiche un joueur spécifique.
     */
    public function show($id)
    {
        $player = Player::with(['user', 'session', 'character'])->find($id);
        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }
        return response()->json($player);
    }

    /**
     * Crée un nouveau joueur.
     */
    public function store(Request $request, $sessionToken)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'class' => 'required|string|max:255',
        ]);

        $session = Session::where('token', $sessionToken)->firstOrFail();

        if ($session->game_master_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $player = new Player([
            'user_id' => $request->user_id,
            'session_id' => $session->session_id,
            'name' => $request->name,
            'class' => $request->class,
        ]);

        $player->save();

        return response()->json(['message' => 'Player created successfully', 'player' => $player], 201);
    }

    /**
     * Met à jour un joueur existant.
     */
    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $session = Session::findOrFail($player->session_id);

        if ($session->game_master_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'class' => 'required|string|max:255',
        ]);

        $player->update($request->only(['name', 'class']));

        return response()->json(['message' => 'Player updated successfully', 'player' => $player]);
    }

    /**
     * Supprime un joueur.
     */
    public function destroy($id)
    {
        $player = Player::findOrFail($id);

        $session = Session::findOrFail($player->session_id);

        if ($session->game_master_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $player->delete();

        return response()->json(['message' => 'Player deleted successfully']);
    }
}