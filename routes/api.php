<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InvitationController;

// Route de test
// Route::get('/test', function () {
//     return response()->json(['message' => 'This is a test']);
// });

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
// Route pour la connexion
Route::post('/login', [AuthController::class, 'login']);
// Route pour la création d'un utilisateur
Route::post('/users', [UserController::class, 'store']);

// Routes nécessitant l'authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Route de déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

    // Routes pour les ressources
    Route::apiResource('users', UserController::class)->except(['store']);
    Route::apiResource('players', PlayerController::class);
    Route::apiResource('sessions', SessionController::class);
    Route::apiResource('shops', ShopController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('characters', CharacterController::class);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('quests', QuestController::class);
    Route::apiResource('transactions', TransactionController::class);

    // Routes pour les items du shop
    Route::get('shops/{shop}/items', [ShopController::class, 'items']);
    Route::post('shops/{shop}/items', [ShopController::class, 'addItem']);
    Route::delete('shops/{shop}/items/{item}', [ShopController::class, 'removeItem']);

    // Routes pour les quêtes
    Route::post('session/{token}/quests', [QuestController::class, 'store']);
    Route::delete('quests/{id}', [QuestController::class, 'destroy']);
    Route::get('session/{token}/quests', [QuestController::class, 'index']);

    // Routes pour les invitations et les sessions
    Route::post('/sessions/{sessionToken}/invite', [InvitationController::class, 'invite']);
    Route::post('/sessions/join', [SessionController::class, 'joinSession']);
    Route::post('/session', [SessionController::class, 'store']);
    Route::get('/session/{sessionToken}', [SessionController::class, 'show']);
    Route::get('/user-sessions', [UserController::class, 'getUserSessions']);

    // Routes pour les profils
    Route::get('/profile', [UserController::class, 'showProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Routes pour les personnages
    Route::post('/session/{sessionToken}/players', [PlayerController::class, 'store']);

    // Routes pour les joueurs
    Route::get('/sessions/{sessionToken}/players', [PlayerController::class, 'index']);
    Route::post('/sessions/{sessionToken}/players', [PlayerController::class, 'store']);
    Route::put('/players/{id}', [PlayerController::class, 'update']);
    Route::delete('/players/{id}', [PlayerController::class, 'destroy']);

    // Routes pour les characters
    Route::get('/characters', [CharacterController::class, 'index']);
    Route::post('/characters', [CharacterController::class, 'store']);
});
