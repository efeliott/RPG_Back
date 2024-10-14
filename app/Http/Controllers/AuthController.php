<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Loggable;

class AuthController extends Controller
{
    use Loggable;

    // Méthode d'inscription
    public function register(Request $request)
    {
        // Validation des données du formulaire
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);
    
        // Si la validation échoue
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Création de l'utilisateur
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        // Générer un token d'authentification
        $token = $user->createToken('RPGToken')->plainTextToken;

        // Utilisation du trait Loggable pour créer un log
        $this->createLog(
            $user->id, 
            'User Registered', 
            'L\'utilisateur a créé un compte avec l\'email: ' . $user->email
        );
    
        // Réponse JSON avec le message de succès et le token
        return response()->json(['message' => 'User registered successfully', 'token' => $token], 201);
    }

    // Méthode de connexion
    public function login(Request $request)
    {
        // Validation des entrées
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Rechercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();
    
        // Vérification des informations de connexion
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    
        // Générer un token pour l'utilisateur
        $token = $user->createToken('auth_token')->plainTextToken;
    
        // Retourner la réponse avec le token d'authentification
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_id' => $user->id,
        ]);
    }
    
    
    // Méthode de déconnexion
    public function logout(Request $request)
    {
        // Récupérer le token de l'utilisateur
        $token = $request->user()->currentAccessToken();
    
        // Révoquer le token
        $token->delete();
    
        return response()->json([
            'message' => 'Logout successful!'
        ]);
    }
    
    // Méthode pour récupérer l'utilisateur connecté
    public function store(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Retourner les erreurs de validation s'il y en a
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Créer un nouvel utilisateur
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Vérifier si l'utilisateur a été créé correctement
        //dd($user);

        // Si le `dd` montre que l'utilisateur a été créé, générer un token
        $token = $user->createToken('RPGToken')->plainTextToken;

        // Retourner une réponse JSON avec l'utilisateur et le token
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}