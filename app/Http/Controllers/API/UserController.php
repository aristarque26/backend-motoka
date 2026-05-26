<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Vérifier si l'utilisateur connecté est super_admin
     */
    private function isSuperAdmin($user)
    {
        return $user->role_enum === 'superAdmin';
    }

    /**
     * Vérifier si l'utilisateur connecté est admin_agence
     */
    private function isAdminAgence($user)
    {
        return $user->role_enum === 'adminAgence';
    }

    /**
     * Lister les utilisateurs (selon le rôle)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($this->isSuperAdmin($user)) {
                $users = User::with('agence')->paginate(20);
            } elseif ($this->isAdminAgence($user)) {
                $users = User::query()->where('Idagence', $user->Idagence)->paginate(20);
            } else {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json($users);
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
     * Voir un utilisateur spécifique
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $targetUser = User::findOrFail($id);

            if (!$this->isSuperAdmin($user) && $targetUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json($targetUser);
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
     * Créer un admin_agence (super_admin uniquement)
     */
    public function storeAdminAgence(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isSuperAdmin($user)) {
                return response()->json(['message' => 'Seul un super_admin peut créer un admin_agence'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'telephone' => 'required|string|max:20',
                'Idagence' => 'required|exists:agences,Idagence',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $newUser = User::create([
                'name' => $request->name,
                'prenom' => $request->prenom ?? '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telephone' => $request->telephone,
                'role_enum' => 'adminAgence',
                'photo' => 'default-avatar.png',
                'Idagence' => $request->Idagence,
            ]);

            return response()->json([
                'message' => 'Admin agence créé avec succès',
                'user' => $newUser
            ], 201);

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
     * Créer un dispatcher (admin_agence ou super_admin)
     */
    public function storeDispatcher(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isSuperAdmin($user) && !$this->isAdminAgence($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            // Déterminer l'Idagence
            if ($this->isSuperAdmin($user)) {
                // Super admin doit fournir Idagence
                if (!$request->Idagence) {
                    return response()->json(['message' => 'Idagence requis pour super_admin'], 422);
                }
                $agenceId = $request->Idagence;
            } else {
                // Admin agence : utiliser son propre Idagence
                $agenceId = $user->Idagence;
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'telephone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $newUser = User::create([
                'name' => $request->name,
                'prenom' => $request->prenom ?? '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telephone' => $request->telephone,
                'role_enum' => 'dispatcher',
                'photo' => 'default-avatar.png',
                'Idagence' => $agenceId,
            ]);

            return response()->json([
                'message' => 'Dispatcher créé avec succès',
                'user' => $newUser
            ], 201);

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
     * Modifier un utilisateur
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $targetUser = User::findOrFail($id);

            if (!$this->isSuperAdmin($user) && $targetUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'telephone' => 'sometimes|string|max:20',
                'password' => 'sometimes|min:6|confirmed',
                'role_enum' => 'sometimes|in:adminAgence,dispatcher,chauffeur',
                'Idagence' => 'sometimes|exists:agences,Idagence',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            if (!isset($data['photo'])) {
                $data['photo'] = $targetUser->photo ?? 'default-avatar.png';
            }

            $targetUser->update($data);

            return response()->json([
                'message' => 'Utilisateur mis à jour',
                'user' => $targetUser
            ]);

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
     * Supprimer un utilisateur
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $targetUser = User::findOrFail($id);

            if (!$this->isSuperAdmin($user) && $targetUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            if ($user->id == $id) {
                return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 403);
            }

            $targetUser->delete();

            return response()->json(['message' => 'Utilisateur supprimé avec succès']);

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
    /**
 * Activer/Désactiver un compte utilisateur (admin uniquement)
 */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $user = $request->user();
            $targetUser = User::findOrFail($id);

            // 1. Vérifier les droits d’accès à l’utilisateur cible (agence)
            if (!$this->isSuperAdmin($user) && $targetUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            // 2. 🔒 Un admin_agence ne peut pas désactiver un autre admin_agence
            if ($this->isAdminAgence($user) && $targetUser->role_enum === 'adminAgence') {
                return response()->json([
                    'message' => 'Vous ne pouvez pas désactiver un autre administrateur d\'agence'
                ], 403);
            }

            // 3. Inverser le statut
            $targetUser->est_actif = !$targetUser->est_actif;
            $targetUser->save();

            $status = $targetUser->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'message' => "Compte {$status} avec succès",
                'user' => $targetUser
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}