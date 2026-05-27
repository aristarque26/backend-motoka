<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AgenceController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ChauffeurController;
use App\Http\Controllers\API\VehiculeController;
use App\Http\Controllers\API\CourseControlleur;
use App\Http\Controllers\API\ColisController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// =============================================
// ROUTES D'AUTHENTIFICATION
// =============================================

Route::get('/test', function () {
    return response()->json([
        'message' => 'API MOTOKA fonctionne',
        'status' => 'success'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-agence', [AgenceController::class, 'registerFull']);

// =============================================
// ROUTE DE DIAGNOSTIC (TEMPORAIRE)
// =============================================

Route::post('/debug-login', function (Request $request) {
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
    
    // Gestion des succursales
    Route::apiResource('succursales', \App\Http\Controllers\API\SuccursaleController::class);
    
    // Gestion des utilisateurs (admin_agence, dispatcher)
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::post('/admin/users/admin-agence', [UserController::class, 'storeAdminAgence']);
    Route::post('/admin/users/dispatcher', [UserController::class, 'storeDispatcher']);
    Route::put('/admin/users/{id}', [UserController::class, 'update']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
    
    // 🔁 Activer/Désactiver un compte utilisateur
    Route::put('/admin/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    
    // =============================================
    // GESTION DES CHAUFFEURS
    // =============================================
    
    // Routes accessibles par l'admin (création du compte chauffeur)
    Route::get('/admin/chauffeurs', [ChauffeurController::class, 'index']);
    Route::post('/admin/chauffeurs', [ChauffeurController::class, 'store']);
    Route::get('/admin/chauffeurs/{id}', [ChauffeurController::class, 'show']);
    Route::put('/admin/chauffeurs/{id}', [ChauffeurController::class, 'updateByAdmin']);
    Route::delete('/admin/chauffeurs/{id}', [ChauffeurController::class, 'destroy']);
    
    // Routes accessibles par le chauffeur (profil)
    Route::get('/chauffeur/profile', [ChauffeurController::class, 'getMyProfile']);
    Route::post('/chauffeur/complete-profile', [ChauffeurController::class, 'completeProfile']);
    Route::put('/chauffeur/profile', [ChauffeurController::class, 'updateMyProfile']);
    
    // =============================================
    // GESTION DES VÉHICULES
    // =============================================
    
    // Voir les véhicules par statut
    Route::get('/vehicules/disponibles', [VehiculeController::class, 'getDisponibles']);
    Route::get('/vehicules/en-mission', [VehiculeController::class, 'getEnMission']);
    Route::get('/vehicules/maintenance', [VehiculeController::class, 'getEnMaintenance']);
    
    // Actions sur les véhicules
    Route::put('/vehicules/{id}/assigner', [VehiculeController::class, 'assignerCourse']);
    Route::put('/vehicules/{id}/liberer', [VehiculeController::class, 'liberer']);
    Route::put('/vehicules/{id}/maintenance', [VehiculeController::class, 'mettreMaintenance']);
    Route::put('/vehicules/{id}/remettre-service', [VehiculeController::class, 'remettreEnService']);
    Route::put('/vehicules/{id}/kilometrage', [VehiculeController::class, 'updateKilometrage']);
    
    // Actions sur les courses
    Route::post('/courses/{id}/colis', [CourseControlleur::class, 'attacherColis']);

    // CRUD standard
    Route::apiResource('vehicules', VehiculeController::class);
    Route::apiResource('courses', CourseControlleur::class);
    Route::apiResource('colis', ColisController::class);
    
    // Dashboard (optionnel)
    Route::get('/dashboard', function () {
        return response()->json([
            'message' => 'Bienvenue sur le tableau de bord MOTOKA',
            'user' => Auth::user()
        ]);
    });
});