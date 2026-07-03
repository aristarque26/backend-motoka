<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AbonnementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Lister tous les plans (ouvert à tous les authentifiés pour info)
     */
    public function index()
    {
        try {
            $plans = Abonnement::all();
            return response()->json($plans);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Voir un plan spécifique
     */
    public function show($id)
    {
        try {
            $plan = Abonnement::findOrFail($id);
            return response()->json($plan);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    /**
     * Mettre à jour les limites d'un plan (super_admin uniquement)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            if ($user->role_enum !== 'superAdmin') {
                return response()->json(['message' => 'Accès non autorisé'], 403);
            }

            $plan = Abonnement::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'description' => 'sometimes|string',
                'prix_mensuel' => 'sometimes|numeric',
                'prix_annuel' => 'sometimes|numeric',
                'max_succursales' => 'sometimes|integer',
                'max_vehicules' => 'sometimes|integer',
                'max_utilisateurs' => 'sometimes|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $plan->update($request->all());

            return response()->json([
                'success' => true,
                'message' => "Le plan {$plan->nomAgence} a été mis à jour avec succès.",
                'data' => $plan
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
