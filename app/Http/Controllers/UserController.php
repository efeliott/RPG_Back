<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Session;


class UserController extends Controller
{
    /**
     * Affiche les informations de l'utilisateur actuel.
     */
    public function show()
    {
        return response()->json(Auth::user());
    }

    /**
     * Met à jour le profil de l'utilisateur.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['username', 'email']));

        return response()->json(['message' => 'Profil mis à jour avec succès']);
    }

    /**
     * Met à jour le mot de passe de l'utilisateur.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    /**
     * Display a listing of the resource.
     */
    public function showProfile()
    {
        $user = Auth::user();
        return response()->json($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'is_admin' => 'required|boolean'
        ]);

        $user = User::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'is_admin' => $validatedData['is_admin']
        ]);

        return response()->json($user, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get the sessions of the user.
     */
    public function getUserSessions()
    {
        $user = Auth::user();

        $gameMasterSessions = $user->sessions()->get();
        $invitedSessions = Session::whereHas('users', function ($query) use ($user) {
            $query->where('users.id', $user->id);
        })->get();

        return response()->json([
            'game_master_sessions' => $gameMasterSessions,
            'invited_sessions' => $invitedSessions
        ]);
    }

    /**
     * Get the user's information.
     */
    public function getUser(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->username,
            'role' => $user->role,
        ]);
    }
}
