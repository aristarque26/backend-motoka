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
        $this->middleware('auth:sanctum');
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

    // Supprimer une agence (super_admin uniquement)
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