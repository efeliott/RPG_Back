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
use App\Http\Controllers\ShopItemController;

// Routes publiques (ex: accès à certaines ressources publiques)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/users', [UserController::class, 'store']);

//Route::get('/sessions/user', [SessionController::class, 'getUserSessions']);
Route::get('/sessions/user', [SessionController::class, 'getUserSessions'])->middleware('auth:sanctum');


// Routes nécessitant l'authentification
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentification et déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Récupération des informations de l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

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

    // Routes pour les items de la boutique
    Route::get('/sessions/{token}/shop-items', [ShopItemController::class, 'index']);
    Route::post('/sessions/{token}/shop-items', [ShopItemController::class, 'store']);
    Route::get('/shop-items/{id}', [ShopItemController::class, 'show']);
    Route::put('/shop-items/{id}', [ShopItemController::class, 'update']);
    Route::delete('/shop-items/{id}', [ShopItemController::class, 'destroy']);

    // Routes pour les quêtes
    Route::get('/sessions/{token}/quests', [QuestController::class, 'index']);
    Route::get('/quests/{id}', [QuestController::class, 'show']);
    Route::post('/sessions/{token}/quests', [QuestController::class, 'store']);
    Route::put('/quests/{id}', [QuestController::class, 'update']);
    Route::put('/quests/{id}/status', [QuestController::class, 'updateStatus']);
    Route::delete('/quests/{id}', [QuestController::class, 'destroy']);

    // Routes pour les invitations et les sessions
    Route::post('/sessions/{sessionToken}/invite', [InvitationController::class, 'invite']);
    Route::post('/sessions/join', [SessionController::class, 'joinSession']);
    Route::get('/session/{sessionToken}', [SessionController::class, 'show']);
    Route::get('/sessions/user', [SessionController::class, 'getUserSessions']);
    Route::delete('/sessions/{sessionToken}', [SessionController::class, 'destroy']);

    // Routes pour les profils
    Route::get('/profile', [UserController::class, 'showProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Routes pour les joueurs
    Route::get('/sessions/{sessionToken}/players', [PlayerController::class, 'index']);
    Route::post('/sessions/{sessionToken}/players', [PlayerController::class, 'store']);
    Route::put('/players/{id}', [PlayerController::class, 'update']);
    Route::delete('/players/{id}', [PlayerController::class, 'destroy']);

    // Routes pour les personnages
    Route::get('/characters', [CharacterController::class, 'index']);
    Route::post('/characters', [CharacterController::class, 'store']);

    // Routes pour le wallet
    Route::post('/sessions/{token}/shop-items/{itemId}/purchase', [ShopItemController::class, 'purchase']);
    Route::get('/sessions/{token}/wallet', [ShopItemController::class, 'getBalance']);
});
