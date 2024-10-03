<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\ShopItem;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('adminOnly');
        $sessions = Session::all();
        return response()->json($sessions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $session = new Session([
            'title' => $request->title,
            'description' => $request->description,
            'game_master_id' => auth()->id(),
            'token' => Str::random(60)
        ]);

        $session->save();

        // Crée un shop pour cette session
        $shop = new Shop([
            'session_id' => $session->session_id,
        ]);

        $shop->save();

        return response()->json([
            'message' => 'Session created successfully!',
            'session' => $session,
            'session_id' => $session->session_id,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    // public function show($token)
    // {
    //     $session = Session::with('players')->findOrFail($token);
    //     return response()->json($session);
    // }

    public function show($token)
    {
        $session = Session::where('token', $token)->with('users')->firstOrFail();

        return response()->json($session);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $session = Session::find($id);
        if (!$session) {
            return response()->json(['message' => 'Session not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'game_master_id' => 'integer|exists:users,user_id',
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $session->update($request->only(['game_master_id', 'title', 'description', 'is_active']));

        return response()->json($session);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($token)
    {
        try {
            // Trouve la session avec le token
            $session = Session::where('token', $token)->first();
    
            if (!$session) {
                return response()->json(['message' => 'Session introuvable.'], 404);
            }
    
            // Supprime d'abord les invitations associées à la session
            Invitation::where('session_id', $session->session_id)->delete();
    
            // Charger les magasins liés à la session
            $shops = Shop::where('session_id', $session->session_id)->get();
    
            foreach ($shops as $shop) {
                // Supprime d'abord les enregistrements de la table shopitems
                ShopItem::where('shop_id', $shop->shop_id)->delete();
    
                // Supprime ensuite le magasin
                $shop->delete();
            }
    
            // Supprime la session elle-même
            $session->delete();
    
            return response()->json(['message' => 'Session supprimée avec succès.'], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la session : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de la session.'], 500);
        }
    }
    
    

    /**
     * Rejoindre une session
     */
    public function joinSession(Request $request)
    {
        try {
            Log::info('joinSession called');
            $token = $request->input('session_token');
            Log::info('Token received:', ['session_token' => $token]);

            if (!Auth::check()) {
                Log::warning('User not authenticated');
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            $invitation = Invitation::where('token', $token)->first();
            Log::info('Invitation:', ['invitation' => $invitation]);

            if ($invitation && !$invitation->accepted) {
                $session = $invitation->session;
                Log::info('Session:', ['session' => $session]);

                $user = Auth::user();
                Log::info('Authenticated user:', ['user' => $user]);

                // Ajouter l'utilisateur à la session
                $session->users()->attach($user->id, ['created_at' => now(), 'updated_at' => now()]);

                // Créer un wallet avec un montant initial de 0 pour l'utilisateur dans cette session
                $wallet = Wallet::create([
                    'user_id' => $user->user_id,
                    'session_id' => $session->session_id,
                    'balance' => 0
                ]);

                $invitation->accepted = true;
                $invitation->save();

                Log::info('User joined session and wallet created successfully');
                return response()->json([
                    'message' => 'You have joined the session!',
                    'session_id' => $session->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                Log::warning('Invalid or already used invitation token');
                return response()->json(['message' => 'Invalid or already used invitation token'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error in joinSession:', ['exception' => $e]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
    
    /**
     * Get the sessions of the user.
     */
    public function getUserSessions()
    {
        $user = Auth::user();
    
        // Récupérer les sessions créées par l'utilisateur en tant que game master
        $gameMasterSessions = Session::where('game_master_id', $user->id)->get();
    
        // Récupérer les sessions où l'utilisateur a été invité
        $invitedSessions = Session::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get();
    
        return response()->json([
            'game_master_sessions' => $gameMasterSessions,
            'invited_sessions' => $invitedSessions
        ]);
    }
}
