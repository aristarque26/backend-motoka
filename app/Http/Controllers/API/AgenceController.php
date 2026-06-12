<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['registerFull']);
        $this->middleware('role:superAdmin')->only(['store', 'update', 'destroy']);
    }

    // Lister toutes les agences
    public function index()
    {
        $agences = Agence::all();
        return response()->json($agences);
    }

    // Voir une agence
    public function show($id)
    {
        $agence = Agence::findOrFail($id);
        return response()->json($agence);
    }

    // Créer une agence (super_admin uniquement)
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:100',
                'slug' => 'required|string|max:100|unique:agences',
                'email' => 'required|email|unique:agences',
                'telephone' => 'required|string|max:20',
                'adresse' => 'nullable|string',
                'logo_url' => 'nullable|string',
                'plan_enum' => 'nullable|in:starter,business,enterprise',
                'statut_enum' => 'nullable|in:actif,suspendu,ferme',
                'ExpirationDate' => 'nullable|date',
                'IdAbonnement' => 'nullable|exists:abonnements,IdAbonnement',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $agence = Agence::create([
                'nom' => $request->nom,
                'slug' => $request->slug,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'logo_url' => $request->logo_url,
                'plan_enum' => $request->plan_enum ?? 'starter',
                'statut_enum' => $request->statut_enum ?? 'actif',
                'ExpirationDate' => $request->ExpirationDate,
                'IdAbonnement' => $request->IdAbonnement,
            ]);

            return response()->json([
                'message' => 'Agence créée avec succès',
                'agence' => $agence
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

    // Créer une agence et son administrateur (SaaS Registration)
    public function registerFull(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Agence
            'agence_nom' => 'required|string|max:100',
            'agence_slug' => 'nullable|string|max:100|unique:agences,slug',
            'agence_email' => 'required|email|unique:agences,email',
            'agence_telephone' => 'required|string|max:20',
            'agence_adresse' => 'nullable|string|max:255',
            
            // Admin
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|min:6',
            'admin_telephone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                // Générer le slug si non fourni
                $slug = $request->agence_slug ?: \Illuminate\Support\Str::slug($request->agence_nom);
                
                // S'assurer que le slug est unique (si généré)
                $originalSlug = $slug;
                $count = 1;
                while (\App\Models\Agence::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }

                // 1. Créer l'agence
                $agence = Agence::create([
                    'nom' => $request->agence_nom,
                    'slug' => $slug,
                    'email' => $request->agence_email,
                    'telephone' => $request->agence_telephone,
                    'adresse' => $request->agence_adresse,
                    'plan_enum' => $request->plan_enum ?? 'starter',
                    'statut_enum' => 'actif',
                ]);

                // 2. Créer l'administrateur
                $admin = \App\Models\User::create([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => \Illuminate\Support\Facades\Hash::make($request->admin_password),
                    'telephone' => $request->admin_telephone,
                    'role_enum' => 'adminAgence',
                    'Idagence' => $agence->Idagence,
                    'est_actif' => true,
                ]);

                return response()->json([
                    'message' => 'Agence et administrateur créés avec succès',
                    'agence' => $agence,
                    'admin' => $admin,
                    'token' => $admin->createToken('auth_token')->plainTextToken,
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Modifier une agence (super_admin uniquement)
    public function update(Request $request, $id)
    {
        try {
            $agence = Agence::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nom' => 'sometimes|string|max:100',
                'slug' => 'sometimes|string|max:100|unique:agences,slug,' . $id,
                'email' => 'sometimes|email|unique:agences,email,' . $id,
                'telephone' => 'sometimes|string|max:20',
                'adresse' => 'nullable|string',
                'logo_url' => 'nullable|string',
                'plan_enum' => 'nullable|in:starter,business,enterprise',
                'statut_enum' => 'nullable|in:actif,suspendu,ferme',
                'ExpirationDate' => 'nullable|date',
                'IdAbonnement' => 'nullable|exists:abonnements,IdAbonnement',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $agence->update($request->only([
                'nom', 'slug', 'email', 'telephone', 'adresse', 
                'logo_url', 'plan_enum', 'statut_enum', 'ExpirationDate', 
                'IdAbonnement'
            ]));

            return response()->json([
                'message' => 'Agence mise à jour avec succès',
                'agence' => $agence
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

    // Obtenir les informations de l'agence de l'utilisateur connecte
    public function getCurrentAgency(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->Idagence) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'Aucune agence associée'
                ]);
            }

            $agence = Agence::with('succursales')->findOrFail($user->Idagence);

            return response()->json([
                'success' => true,
                'data' => $agence
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->role_enum !== 'adminAgence' && $user->role_enum !== 'superAdmin') {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $agence = Agence::findOrFail($user->Idagence);
            
            $validator = Validator::make($request->all(), [
                'nomAgence' => 'sometimes|string|max:255',
                'nom' => 'sometimes|string|max:255',
                'adresse' => 'sometimes|string',
                'telephone' => 'sometimes|string',
                'email' => 'sometimes|email',
                'config_prix_km' => 'nullable|numeric',
                'config_frais_adhesion' => 'nullable|numeric',
                'config_commission_defaut' => 'nullable|numeric',
                'logo_url' => 'nullable|string',
                'couleur_primaire' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            if (isset($data['nomAgence'])) {
                $data['nom'] = $data['nomAgence'];
                unset($data['nomAgence']);
            }

            $agence->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres de l\'agence mis à jour',
                'data' => $agence
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $agence = Agence::findOrFail($id);
            $agence->delete();

            return response()->json([
                'message' => 'Agence supprimée avec succès'
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
