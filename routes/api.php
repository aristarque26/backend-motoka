<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AgenceController;
use App\Http\Controllers\API\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// =============================================
// ROUTES D'AUTHENTIFICATION
// =============================================

Route::post('/login', [AuthController::class, 'login']);

// =============================================
// ROUTE DE DIAGNOSTIC (TEMPORAIRE)
// =============================================

Route::post('/debug-login', function (Request $request) {
    // Afficher tout ce que Postman a envoyé
    return response()->json([
        'all_input' => $request->all(),
        'headers' => $request->headers->all(),
        'email_input' => $request->input('email'),
        'email_get' => $request->get('email'),
        'email_request' => $request->email,
    ]);
});
// =============================================
// ROUTES PROTÉGÉES
// =============================================

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Gestion des agences (super_admin uniquement)
    Route::apiResource('agences', AgenceController::class);
    
    // Gestion des utilisateurs (admin_agence, dispatcher)
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::post('/admin/users/admin-agence', [UserController::class, 'storeAdminAgence']);
    Route::post('/admin/users/dispatcher', [UserController::class, 'storeDispatcher']);
    Route::put('/admin/users/{id}', [UserController::class, 'update']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
    
    // Dashboard (optionnel)
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Bienvenue sur le tableau de bord MOTOKA',
            'user' => Auth::user()
        ]);
    });
});