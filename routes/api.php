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
use App\Http\Controllers\SessionManagementController;

Route::options('/{any}', function () {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');

Route::post('/debug', function () {
    return response()->json(['message' => 'Debug route reached'], 200);
});


// Routes publiques (ex: accès à certaines ressources publiques)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/users', [UserController::class, 'store']);

//Route::get('/sessions/user', [SessionController::class, 'getUserSessions']);
Route::get('/sessions/user', [SessionController::class, 'getUserSessions'])->middleware('auth:sanctum');

Route::get('/test', function () {
    return ['message' => 'API working'];
});

// Routes nécessitant l'authentification
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentification et déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Récupération des informations de l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/game-master/{sessionToken}', [SessionController::class, 'showSessionDetails']);

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

    Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUser']);

    // Routes pour les items de la boutique
    Route::post('/items', [ItemController::class, 'createItemWithoutShop']);

    // Routes pour les inventaires
    Route::get('/characters/{characterId}/inventory', [InventoryController::class, 'getInventory']);
    Route::delete('/inventory/{inventoryId}', [InventoryController::class, 'deleteInventoryItem']);

    // Routes pour les quêtes
    Route::prefix('quests')->group(function () {
        Route::get('/{sessionId}/quests', [QuestController::class, 'getQuests']); // Récupérer toutes les quêtes d'une session
        Route::post('/', [QuestController::class, 'store']); // Créer une quête
        Route::put('/{questId}', [QuestController::class, 'update']); // Modifier une quête
        Route::delete('/{questId}', [QuestController::class, 'destroy']); // Supprimer une quête
        Route::post('/{questId}/assign', [QuestController::class, 'assignToPlayer']); // Attribuer à un joueur
        Route::post('/{questId}/select/{playerId}', [QuestController::class, 'selectQuest']); // Joueur choisit une quête
        Route::post('/{questId}/complete', [QuestController::class, 'markAsComplete']);
    });

    // Routes pour la gestions de sactifsctions palyer
    Route::prefix('player')->group(function () {
        // Routes pour la gestion de l'inventaire du joueur
        Route::get('/{sessionId}/inventory/{characterId}', [InventoryController::class, 'getPlayerInventory']);
        Route::get('/inventory/item/{inventoryId}', [InventoryController::class, 'getItemDetail']);
        Route::get('/{sessionId}/character', [PlayerController::class, 'getCharacterForSession']);
        Route::get('{sessionId}/wallet/{characterId}', [PlayerController::class, 'getWalletBalance']);
        Route::get('{sessionId}/shop', [PlayerController::class, 'getShopItems']);
        Route::get('{sessionId}/quests/{characterId}', [PlayerController::class, 'getAvailableQuests']);
        Route::post('/shop/{sessionId}/purchase/{itemId}/{characterId}', [ShopController::class, 'purchaseItem']);
        Route::post('/{sessionId}/quests/{questId}/accept/{characterId}', [QuestController::class, 'acceptQuest']);
    });

    // Routes pour les invitations et les sessions
    Route::post('/sessions', [SessionController::class, 'store']);
    Route::post('/sessions/{sessionToken}/invite', [InvitationController::class, 'invite']);
    Route::post('/join-session', [SessionController::class, 'joinSession']);
    Route::get('/session/{sessionToken}', [SessionController::class, 'show']);
    Route::get('/sessions/user', [SessionController::class, 'getUserSessions']);
    Route::delete('/sessions/{sessionToken}', [SessionController::class, 'destroy']);

    // Routes pour les profils
    Route::get('/profile', [UserController::class, 'showProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
  
    // Routes pour le management de session
    Route::prefix('session-management')->group(function () {
        // Gestion des personnages
        Route::get('/{sessionId}/characters', [CharacterController::class, 'getCharacters']);
        Route::post('/{sessionId}/character', [CharacterController::class, 'createCharacter']);
        Route::delete('/character/{characterId}', [CharacterController::class, 'deleteCharacter']);
        Route::put('/character/{characterId}', [CharacterController::class, 'updateCharacter']);
        // Gestion des inventaires
        Route::post('/characters/{characterId}/inventory', [ItemController::class, 'addItemToInventory']);
        Route::get('/character/{characterId}/inventory', [SessionManagementController::class, 'getInventory']);
        Route::get('/sessions/{session_id}/users-without-character', [SessionController::class, 'getUsersWithoutCharacter']);
        // Gestions des wallets
        Route::get('/{sessionId}/wallets', [SessionManagementController::class, 'getWallets']);
        Route::put('/{sessionId}/user/{userId}/wallet', [SessionManagementController::class, 'addMoneyToWallet']);
        Route::put('/wallet/{walletId}', [SessionManagementController::class, 'updateWallet']);
        // Gestion des items de la boutique
        Route::get('/{sessionId}/shop-items', [SessionManagementController::class, 'getShopItems']);
        Route::post('/{sessionId}/shop-items', [SessionManagementController::class, 'addItemToShop']);
        Route::delete('/shop-item/{shopId}/{itemId}', [SessionManagementController::class, 'removeItemFromShop']);
        // Gestion du shop
        Route::get('/{sessionId}/shop/items', [ShopController::class, 'getShopItems']);
        Route::post('/{sessionId}/shop/add-item', [ShopController::class, 'addItemToShop']);
        Route::delete('/{sessionId}/shop/items/{itemId}', [ShopController::class, 'deleteItemFromShop']);
        Route::put('/{sessionId}/shop/items/{itemId}', [ShopController::class, 'updateItemInShop']);
    });
});