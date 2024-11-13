<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Character;
use App\Models\Session;
use App\Models\Wallet;
use App\Models\ShopItem;
use App\Models\Quest;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Récupère le personnage de l'utilisateur pour une session donnée.
     */
    public function getCharacterForSession($sessionId)
    {
        $userId = Auth::id();

        // Vérifie si l'utilisateur est invité dans la session
        $session = Session::where('session_id', $sessionId)
                          ->whereHas('users', function($query) use ($userId) {
                              $query->where('user_id', $userId);
                          })
                          ->first();

        if (!$session) {
            return response()->json(['message' => 'User not part of this session'], 403);
        }

        // Récupère le personnage de cet utilisateur pour la session donnée
        $character = Character::where('session_id', $sessionId)
                              ->where('user_id', $userId)
                              ->first();

        if (!$character) {
            return response()->json(['message' => 'Character not found for user in this session'], 404);
        }

        return response()->json($character);
    }

    /**
     * Récupère le solde du porte-monnaie pour un personnage d'une session donnée.
     */
    public function getWalletBalance($sessionId, $characterId)
    {
        $wallet = Wallet::where('session_id', $sessionId)
                        ->where('character_id', $characterId)
                        ->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        return response()->json(['balance' => $wallet->balance]);
    }

    /**
     * Récupère les items disponibles dans le magasin pour une session donnée.
     */
    public function getShopItems($sessionId)
    {
        $session = Session::findOrFail($sessionId);

        $shop = Shop::where('session_id', $session->session_id)->first();

        $shopItems = ShopItem::where('shop_id', $shop->shop_id)
            ->with('item')
            ->get()
            ->map(function ($shopItem) {
                return [
                    'item_id' => $shopItem->item->item_id,
                    'title' => $shopItem->item->title,
                    'description' => $shopItem->item->description,
                    'price' => $shopItem->price,
                ];
            });

        return response()->json($shopItems);
    }

    /**
     * Récupère les quêtes disponibles (non terminées) pour une session donnée.
     */
    public function getAvailableQuests($sessionId, $characterId)
    {
        $session = Session::findOrFail($sessionId);

        $quests = Quest::where('session_id', $session->session_id)
            ->where('is_finished', false)
            ->whereNull('player_id')
            ->get(['quest_id', 'title', 'description', 'reward', 'is_finished', 'character_id']);

        return response()->json($quests);
    }
}