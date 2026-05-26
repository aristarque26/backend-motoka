<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Connexion (admin uniquement)
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Identifiants incorrects'
                ], 401);
            }

            // 🔒 Vérifier que le compte est actif
            if (!$user->est_actif) {
                return response()->json([
                    'message' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.'
                ], 403);
            }

            $allowedRoles = ['superAdmin', 'adminAgence', 'dispatcher', 'chauffeur'];
            if (!in_array($user->role_enum, $allowedRoles)) {
                return response()->json([
                    'message' => 'Accès non autorisé. Zone admin uniquement.'
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'role' => $user->role_enum,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Utilisateur connecté
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user('sanctum');
            
            if ($user && $user->role_enum === 'chauffeur') {
                $user->load('chauffeur');
            }
            
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user('sanctum');
            if ($user instanceof User) {
                $token = $user->currentAccessToken();
                if ($token) {
                    $token->delete();
                }
            }
            return response()->json([
                'message' => 'Déconnexion réussie.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}