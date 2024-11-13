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
use App\Models\SessionUser;

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

    /**
     * Display the specified resource.
     */
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
        // Vérification de l'authentification de l'utilisateur
        if (!Auth::check()) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user = Auth::user();
        $sessionToken = $request->input('session_token');

        // Récupérer l'invitation via le token d'invitation
        $invitation = Invitation::where('token', $sessionToken)->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invalid or expired invitation token'], 404);
        }

        // Récupérer la session liée à cette invitation
        $session = Session::find($invitation->session_id);

        if (!$session || !$session->is_active) {
            return response()->json(['message' => 'Session not found or inactive'], 404);
        }

        // Vérifier si l'utilisateur est déjà lié à la session
        $isUserInSession = SessionUser::where('session_id', $session->session_id)
                                    ->where('user_id', $user->id)
                                    ->exists();

        if ($isUserInSession) {
            return response()->json(['message' => 'User already in session'], 400);
        }

        // Lier l'utilisateur à la session
        SessionUser::create([
            'session_id' => $session->session_id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Marquer l'invitation comme utilisée
        $invitation->update(['accepted' => true]);

        return response()->json([
            'message' => 'You have successfully joined the session!',
            'session_id' => $session->session_id,
        ], 200);
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

    /**
     * Show the details of a session.
     */
    public function showSessionDetails($sessionToken)
    {
        $session = Session::where('token', $sessionToken)->with('users')->firstOrFail();

        return response()->json($session);
    }

    /**
     * Get the users without a character in a session.
     */
    public function getUsersWithoutCharacter($session_id)
    {
        $session = Session::findOrFail($session_id);

        $usersWithoutCharacter = $session->users()
            ->whereDoesntHave('characters', function ($query) use ($session_id) {
                $query->where('session_id', $session_id);
            })
            ->get();

        return response()->json($usersWithoutCharacter);
    }
}
