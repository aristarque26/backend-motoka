<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Chauffeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChauffeurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Vérifier si l'utilisateur connecté est admin_agence ou super_admin
     */
    private function isAdminOrSuperAdmin($user)
    {
        return in_array($user->role_enum, ['adminAgence', 'superAdmin']);
    }

    /**
     * Vérifier si l'utilisateur connecté est chauffeur
     */
    private function isChauffeur($user)
    {
        return $user->role_enum === 'chauffeur';
    }

    // =============================================
    // PARTIE ADMIN (création du compte chauffeur)
    // =============================================

    /**
     * Lister tous les chauffeurs (admin uniquement)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            if ($user->role_enum === 'superAdmin') {
                $chauffeurs = User::where('role_enum', 'chauffeur')
                    ->with('chauffeur')
                    ->paginate(20);
            } else {
                $chauffeurs = User::where('role_enum', 'chauffeur')
                    ->where('Idagence', $user->Idagence)
                    ->with('chauffeur')
                    ->paginate(20);
            }

            return response()->json($chauffeurs);

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
     * Créer un compte chauffeur (admin uniquement)
     * Crée uniquement l'utilisateur, pas encore la fiche chauffeur
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            // Déterminer l'agence
            $agenceId = $user->role_enum === 'superAdmin'
                ? $request->Idagence
                : $user->Idagence;

            if ($user->role_enum === 'superAdmin' && !$request->Idagence) {
                return response()->json(['message' => 'Idagence requis pour super_admin'], 422);
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

            // Création du compte utilisateur (authentification)
            $newUser = User::create([
                'name' => $request->name,
                'prenom' => $request->prenom ?? '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telephone' => $request->telephone,
                'role_enum' => 'chauffeur',
                'photo' => 'default-avatar.png',
                'Idagence' => $agenceId,
                'est_actif' => true,
            ]);

            return response()->json([
                'message' => 'Compte chauffeur créé avec succès. Le chauffeur doit maintenant compléter son profil.',
                'user' => $newUser,
                'email' => $newUser->email,
                'temp_password' => $request->password
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
     * Voir un chauffeur spécifique (admin uniquement)
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $chauffeurUser = User::where('role_enum', 'chauffeur')
                ->with('chauffeur')
                ->findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $chauffeurUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            return response()->json($chauffeurUser);

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
     * Modifier un chauffeur (admin uniquement) - modification des infos métier
     */
    public function updateByAdmin(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $chauffeurUser = User::where('role_enum', 'chauffeur')
                ->with('chauffeur')
                ->findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $chauffeurUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'nomChauffeur' => 'sometimes|string|max:255',
                'telephone' => 'sometimes|string|max:20',
                'adresse' => 'nullable|string',
                'numeroPermis' => 'sometimes|string|unique:chauffeurs,numeroPermis,' . ($chauffeurUser->chauffeur->Idchauffeur ?? 0),
                'statut_Enum' => 'sometimes|in:dispo,en_course,conge,suspendu',
                'type_contrat' => 'sometimes|in:salarie,adherent',
                'salaireBase' => 'sometimes|numeric|min:0',
                'commission' => 'sometimes|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Mise à jour du user
            if ($request->has('name')) {
                $chauffeurUser->name = $request->name;
            }
            if ($request->has('prenom')) {
                $chauffeurUser->prenom = $request->prenom;
            }
            if ($request->has('telephone')) {
                $chauffeurUser->telephone = $request->telephone;
            }
            $chauffeurUser->save();

            // Mise à jour ou création de la fiche chauffeur
            if ($chauffeurUser->chauffeur) {
                $oldType = $chauffeurUser->chauffeur->type_contrat;
                $chauffeurUser->chauffeur->update($request->only([
                    'nomChauffeur', 'telephone', 'adresse', 'numeroPermis',
                    'statut_Enum', 'type_contrat', 'salaireBase', 'commission'
                ]));

                // Logic for adhesion fee
                if ($request->type_contrat === 'adherent' && $oldType !== 'adherent') {
                    \App\Models\TransactionFinance::create([
                        'montant' => $request->frais_adhesion ?? 50, // Default 50 or from request
                        'devise' => 'USD',
                        'mode_paiement_Enum' => $request->mode_paiement ?? 'especes',
                        'Type_Transaction_Enum' => 'autre',
                        'description' => 'Frais d\'adhésion chauffeur partenaire',
                        'Date_Paiement' => now(),
                        'Idagence' => $chauffeurUser->Idagence,
                        'Idchauffeur' => $chauffeurUser->chauffeur->Idchauffeur
                    ]);
                }
            } else {
                // Si la fiche n'existe pas encore, on la crée
                $chauffeur = Chauffeur::create([
                    'nomChauffeur' => $request->nomChauffeur ?? $chauffeurUser->name,
                    'telephone' => $request->telephone ?? $chauffeurUser->telephone,
                    'adresse' => $request->adresse,
                    'numeroPermis' => $request->numeroPermis,
                    'DateValidite' => $request->DateValidite ?? now()->addYears(5),
                    'statut_Enum' => $request->statut_Enum ?? 'dispo',
                    'type_contrat' => $request->type_contrat ?? 'salarie',
                    'salaireBase' => $request->salaireBase ?? 0,
                    'DateEmbauche' => now(),
                    'commission' => $request->commission ?? 0,
                    'revenu' => 0,
                    'NbreCourse' => 0,
                    'NoteMoyenne' => 0,
                    'Idutilisateur' => $chauffeurUser->id,
                    'Idagence' => $chauffeurUser->Idagence,
                ]);

                if ($request->type_contrat === 'adherent') {
                    \App\Models\TransactionFinance::create([
                        'montant' => $request->frais_adhesion ?? 50,
                        'devise' => 'USD',
                        'mode_paiement_Enum' => $request->mode_paiement ?? 'especes',
                        'Type_Transaction_Enum' => 'autre',
                        'description' => 'Frais d\'adhésion chauffeur partenaire',
                        'Date_Paiement' => now(),
                        'Idagence' => $chauffeurUser->Idagence,
                        'Idchauffeur' => $chauffeur->Idchauffeur
                    ]);
                }
            }

            return response()->json([
                'message' => 'Chauffeur mis à jour avec succès',
                'user' => $chauffeurUser->fresh('chauffeur')
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
     * Supprimer un chauffeur (admin uniquement)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$this->isAdminOrSuperAdmin($user)) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $chauffeurUser = User::where('role_enum', 'chauffeur')->findOrFail($id);

            if ($user->role_enum !== 'superAdmin' && $chauffeurUser->Idagence !== $user->Idagence) {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            // Supprimer la fiche chauffeur si elle existe
            if ($chauffeurUser->chauffeur) {
                $chauffeurUser->chauffeur->delete();
            }

            // Supprimer l'utilisateur
            $chauffeurUser->delete();

            return response()->json(['message' => 'Chauffeur supprimé avec succès']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    // =============================================
    // PARTIE CHAUFFEUR (gestion de son propre profil)
    // =============================================

    /**
     * Le chauffeur voit son propre profil
     */
    public function getMyProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isChauffeur($user)) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            $user->load('chauffeur');

            return response()->json([
                'user' => $user,
                'chauffeur' => $user->chauffeur
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Le chauffeur complète son profil (première fois)
     * Le chauffeur ne remplit que ses informations personnelles
     * Le salaireBase et commission sont définis par l'agence (0 par défaut)
     */
    public function completeProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            // Vérifier que l'utilisateur est bien un chauffeur
            if ($user->role_enum !== 'chauffeur') {
                return response()->json([
                    'message' => 'Accès non autorisé. Seuls les chauffeurs peuvent compléter leur profil.'
                ], 403);
            }
            
            // Vérifier si le chauffeur a déjà un profil
            $existingChauffeur = Chauffeur::where('Idutilisateur', $user->id)->first();
            
            // =============================================
            // VALIDATION (chauffeur uniquement)
            // =============================================
            $rules = [
                'numeroPermis' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[A-Z0-9]{5,20}$/',
                    'unique:chauffeurs,numeroPermis,' . ($existingChauffeur->Idchauffeur ?? 'NULL') . ',Idchauffeur'
                ],
                'DateValidite' => 'required|date|after:today',
                'adresse' => 'nullable|string|max:255',
            ];
            
            $messages = [
                'numeroPermis.required' => 'Le numéro du permis de conduire est obligatoire.',
                'numeroPermis.regex' => 'Le numéro de permis doit contenir uniquement des lettres majuscules et des chiffres (5 à 20 caractères).',
                'numeroPermis.unique' => 'Ce numéro de permis est déjà enregistré.',
                'DateValidite.required' => 'La date de validité du permis est obligatoire.',
                'DateValidite.after' => 'La date de validité doit être postérieure à aujourd\'hui.',
            ];
            
            $validated = $request->validate($rules, $messages);
            
            // =============================================
            // PRÉPARATION DES DONNÉES
            // =============================================
            $data = [
                'numeroPermis' => strtoupper($request->numeroPermis),
                'DateValidite' => $request->DateValidite,
                'adresse' => $request->adresse,
            ];
            
            // =============================================
            // CRÉATION OU MISE À JOUR
            // =============================================
            if ($existingChauffeur) {
                // Mise à jour existante
                $existingChauffeur->update($data);
                
                return response()->json([
                    'message' => 'Profil chauffeur mis à jour avec succès',
                    'chauffeur' => $existingChauffeur
                ], 200);
            }
            
            // Création du profil (première fois)
            // Les champs salaireBase et commission restent à 0 par défaut
            // L'admin_agence devra les mettre à jour plus tard
            $chauffeur = Chauffeur::create([
                'nomChauffeur' => $user->name,
                'telephone' => $user->telephone,
                'adresse' => $request->adresse,
                'numeroPermis' => strtoupper($request->numeroPermis),
                'Url_Permis' => null,
                'photo' => null,
                'DateValidite' => $request->DateValidite,
                'statut_Enum' => 'dispo',
                'salaireBase' => 0,
                'DateEmbauche' => now(),
                'commission' => 0,
                'revenu' => 0,
                'NbreCourse' => 0,
                'NoteMoyenne' => 0,
                'Idutilisateur' => $user->id,
                'Idagence' => $user->Idagence,
            ]);
            
            return response()->json([
                'message' => 'Profil chauffeur complété avec succès',
                'chauffeur' => $chauffeur
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Le chauffeur modifie son propre profil
     */
    public function updateMyProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$this->isChauffeur($user)) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }

            // Vérifier qu'il a déjà un profil
            if (!$user->chauffeur) {
                return response()->json(['message' => 'Veuillez d\'abord compléter votre profil'], 400);
            }

            $validator = Validator::make($request->all(), [
                'adresse' => 'nullable|string',
                'photo' => 'nullable|string',
                'Url_Permis' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Mise à jour du user
            if ($request->has('telephone')) {
                $user->telephone = $request->telephone;
                $user->save();
            }

            // Mise à jour du chauffeur
            $user->chauffeur->update($request->only([
                'adresse', 'photo', 'Url_Permis'
            ]));

            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'chauffeur' => $user->chauffeur->fresh()
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